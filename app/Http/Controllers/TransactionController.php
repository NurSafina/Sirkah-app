<?php

namespace App\Http\Controllers;

use App\Models\BalanceMutation;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['student', 'user', 'items.product'])
            ->latest()
            ;
        if (auth()->user()->isCashier()) {
            $query->where('user_id', auth()->id());
        }
        $transactions = $query->paginate(15);
        $students = Student::where('status', 'active')->orderBy('name')->get();
        $editingTransaction = $request->filled('edit')
            ? Transaction::with('items')->findOrFail($request->integer('edit'))
            : null;

        return view('transactions.index', compact('transactions', 'students', 'editingTransaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $transaction) {
            $transaction = Transaction::with('items')->lockForUpdate()->findOrFail($transaction->id);
            $oldStudentId = $transaction->student_id;
            $needsRebuild = $transaction->status !== $data['status']
                || (int) $oldStudentId !== (int) $data['student_id'];

            if ($needsRebuild && $transaction->status === 'completed') {
                $this->restoreTransactionEffects($transaction);
            }

            if ($needsRebuild && $data['status'] === 'completed') {
                $transaction->setAttribute('student_id', $data['student_id']);
                $this->applyTransactionEffects($transaction);
            }

            $transaction->update([
                'student_id' => $data['student_id'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($transaction->status !== 'completed') {
            DB::transaction(function () use ($transaction) {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'transaction.deleted',
                    'auditable_type' => Transaction::class,
                    'auditable_id' => $transaction->id,
                    'old_values' => ['transaction_code' => $transaction->transaction_code, 'status' => $transaction->status, 'total' => $transaction->total],
                    'ip_address' => request()->ip(),
                ]);
                $transaction->delete();
            });

            return redirect()->route('transactions.index')->with('success', 'Transaksi tidak aktif berhasil dihapus. Histori mutasi saldo dan stok tetap dipertahankan.');
        }

        DB::transaction(function () use ($transaction) {
            $transaction = Transaction::with('items')->lockForUpdate()->findOrFail($transaction->id);
            $this->restoreTransactionEffects($transaction);
            $transaction->update([
                'status' => 'cancelled',
                'notes' => trim(($transaction->notes ? $transaction->notes . "\n" : '') . 'Dibatalkan oleh ' . auth()->user()->name),
            ]);
            AuditLog::create([
                'user_id' => auth()->id(), 'action' => 'transaction.cancelled', 'auditable_type' => Transaction::class,
                'auditable_id' => $transaction->id, 'new_values' => ['status' => 'cancelled', 'reason' => $transaction->notes],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dibatalkan. Saldo dan stok dikembalikan.');
    }

    private function restoreTransactionEffects(Transaction $transaction): void
    {
        $student = $transaction->student_id
            ? Student::whereKey($transaction->student_id)->lockForUpdate()->first()
            : null;

        if ($student) {
            $balanceBefore = (float) $student->balance;
            $student->increment('balance', $transaction->total);
            BalanceMutation::create([
                'student_id' => $student->id,
                'user_id' => auth()->id(),
                'type' => 'refund',
                'amount' => $transaction->total,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + (float) $transaction->total,
                'description' => 'Pembatalan transaksi ' . $transaction->transaction_code,
                'reference_type' => 'transaction',
                'reference_id' => $transaction->id,
            ]);
        }

        foreach ($transaction->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if (! $product) {
                continue;
            }

            $product->increment('stock', $item->quantity);
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $item->quantity,
                'reason' => 'Pengembalian stok transaksi ' . $transaction->transaction_code,
                'reference_type' => 'transaction',
                'reference_id' => $transaction->id,
            ]);
        }
    }

    private function applyTransactionEffects(Transaction $transaction): void
    {
        $student = Student::whereKey($transaction->student_id)->lockForUpdate()->firstOrFail();
        if ((float) $student->balance < (float) $transaction->total) {
            abort(422, 'Saldo siswa tidak mencukupi untuk transaksi ini.');
        }

        foreach ($transaction->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            if ($product->stock < $item->quantity) {
                abort(422, 'Stok produk ' . $product->name . ' tidak mencukupi.');
            }

            $product->decrement('stock', $item->quantity);
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $item->quantity,
                'reason' => 'Penjualan POS',
                'reference_type' => 'transaction',
                'reference_id' => $transaction->id,
            ]);
        }

        $student->decrement('balance', $transaction->total);
        BalanceMutation::create([
            'student_id' => $student->id,
            'user_id' => auth()->id(),
            'type' => 'purchase',
            'amount' => -$transaction->total,
            'description' => 'Pembelian produk makanan',
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
        ]);
    }
}
