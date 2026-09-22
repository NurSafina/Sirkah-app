<?php

namespace App\Http\Controllers;

use App\Models\BalanceMutation;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\WhatsAppNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $students = Student::where('status', 'active')
            ->withSum(['transactions as spent_today' => fn ($query) => $query->whereDate('created_at', today())->where('status', 'completed')], 'total')
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('products', 'students'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'items' => ['required', 'array'],
        ]);

        $selectedItems = [];
        $grandTotal = 0;

        $student = Student::findOrFail($request->student_id);

        foreach ($request->input('items', []) as $productId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity <= 0) {
                continue;
            }

            $product = Product::whereKey($productId)->where('is_active', true)->first();
            if (! $product) {
                continue;
            }

            if ($product->stock < $quantity) {
                return back()->withErrors(['items' => 'Stok tidak mencukupi untuk produk ' . $product->name . '.'])->withInput();
            }

            $subtotal = (float) $product->price * $quantity;
            $grandTotal += $subtotal;
            $selectedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($selectedItems)) {
            return back()->withErrors(['items' => 'Pilih minimal satu produk untuk transaksi.'])->withInput();
        }

        $transaction = DB::transaction(function () use ($request, $selectedItems, $grandTotal) {
            $student = Student::whereKey($request->student_id)->lockForUpdate()->firstOrFail();

            if ($student->status !== 'active' || $student->card_status !== 'active') {
                abort(422, 'Kartu atau data santri tidak aktif.');
            }

            if ((float) $student->balance < $grandTotal) {
                abort(422, 'Saldo siswa tidak mencukupi untuk transaksi ini.');
            }

            if ((float) $student->daily_limit > 0) {
                $spentToday = (float) $student->transactions()
                    ->whereDate('created_at', today())
                    ->where('status', 'completed')
                    ->sum('total');

                if ($spentToday + $grandTotal > (float) $student->daily_limit) {
                    abort(422, 'Transaksi melebihi limit jajan harian siswa.');
                }
            }

            $transaction = Transaction::create([
                'student_id' => $student->id,
                'user_id' => auth()->id(),
                'transaction_code' => 'TRX-' . strtoupper(uniqid()),
                'total' => $grandTotal,
                'payment_type' => 'saldo',
                'status' => 'completed',
                'notes' => 'Pembelian di kasir',
            ]);

            foreach ($selectedItems as $item) {
                $product = Product::whereKey($item['product']->id)->lockForUpdate()->firstOrFail();
                $quantity = $item['quantity'];

                if (! $product->is_active || $product->stock < $quantity) {
                    abort(422, 'Stok produk ' . $product->name . ' berubah atau tidak mencukupi.');
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $product->price * $quantity,
                ]);

                $product->decrement('stock', $quantity);
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $quantity,
                    'reason' => 'Penjualan POS',
                    'reference_type' => 'transaction',
                    'reference_id' => $transaction->id,
                ]);
            }

            $balanceBefore = (float) $student->balance;
            $student->decrement('balance', $grandTotal);
            BalanceMutation::create([
                'student_id' => $student->id,
                'user_id' => auth()->id(),
                'type' => 'purchase',
                'amount' => -$grandTotal,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $grandTotal,
                'description' => 'Pembelian produk makanan',
                'reference_type' => 'transaction',
                'reference_id' => $transaction->id,
            ]);

            return $transaction;
        });

        $student = Student::find($transaction->student_id);
        $phone = PhoneNumber::normalize($student?->parent_phone);
        if ($student && $phone) {
            $itemsText = $transaction->items->map(fn ($item) => $item->product_name . ' (' . $item->quantity . ')')->implode(', ');
            $message = "Sirkah - Informasi Belanja\nSantri: {$student->name}\nBelanja: {$itemsText}\nTotal: Rp " . number_format($transaction->total, 0, ',', '.') . "\nSaldo tersisa: Rp " . number_format($student->balance, 0, ',', '.');
            $notification = WhatsAppNotification::firstOrCreate([
                'transaction_id' => $transaction->id,
            ], [
                'student_id' => $student->id,
                'transaction_id' => $transaction->id,
                'phone_number' => $phone,
                'message' => $message,
                'message_hash' => hash('sha256', $phone . '|' . $message),
                'status' => 'pending',
            ]);
            AuditLog::create([
                'user_id' => auth()->id(), 'action' => 'transaction.completed', 'auditable_type' => Transaction::class,
                'auditable_id' => $transaction->id, 'new_values' => ['total' => $grandTotal, 'student_id' => $student->id],
                'ip_address' => request()->ip(),
            ]);
            if ($notification->wasRecentlyCreated) SendWhatsAppNotification::dispatch($notification);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diproses.');
    }
}
