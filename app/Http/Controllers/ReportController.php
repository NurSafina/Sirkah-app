<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Student;
use App\Models\BalanceMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()->where('status', 'completed');
        $this->applyFilters($query, $request);
        $totalRevenue = (float) (clone $query)->sum('total');
        $totalExpenses = (float) Product::sum(DB::raw('cost_price * stock'));

        $dailyRevenue = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $stockSummary = Product::query()
            ->select('name', 'category', 'stock', 'price', 'cost_price')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $topProducts = TransactionItem::query()
            ->whereHas('transaction', function ($transactionQuery) use ($request) {
                $transactionQuery->where('status', 'completed');
                $this->applyFilters($transactionQuery, $request);
            })
            ->selectRaw('product_name, SUM(quantity) as sold, SUM(subtotal) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        $dailyStudentSpending = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total_spent')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $profit = $totalRevenue - $totalExpenses;

        $cashiers = User::where('role', 'cashier')->orderBy('name')->get();
        $students = Student::orderBy('name')->get(['id', 'name', 'student_number']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $totalTopups = (float) BalanceMutation::where('type', 'topup')
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->sum('amount');
        $studentBalances = Student::orderBy('name')->get(['name', 'balance']);
        return view('reports.index', compact(
            'totalRevenue',
            'totalExpenses',
            'dailyRevenue',
            'stockSummary',
            'topProducts',
            'dailyStudentSpending', 'cashiers', 'students', 'products',
            'totalTopups', 'studentBalances', 'profit'
        ));
    }

    public function export(Request $request)
    {
        $query = Transaction::with(['student', 'user'])->where('status', 'completed');
        $this->applyFilters($query, $request);
        $transactions = $query->latest()->get();

        if ($request->query('format') === 'pdf') {
            return Pdf::loadView('reports.pdf', [
                'transactions' => $transactions,
                'dateFrom' => $request->query('date_from'),
                'dateTo' => $request->query('date_to'),
            ])->setPaper('a4', 'landscape')->download('laporan-penjualan-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->query('format') === 'xls') {
            return response()->streamDownload(function () use ($transactions) {
                echo "<table><tr><th>Kode</th><th>Santri</th><th>Kasir</th><th>Metode</th><th>Total</th><th>Waktu</th></tr>";
                foreach ($transactions as $transaction) {
                    echo '<tr><td>' . e($transaction->transaction_code) . '</td><td>' . e($transaction->student?->name ?? '-') . '</td><td>' . e($transaction->user?->name ?? '-') . '</td><td>' . e($transaction->payment_type) . '</td><td>' . e($transaction->total) . '</td><td>' . e($transaction->created_at) . '</td></tr>';
                }
                echo '</table>';
            }, 'laporan-penjualan-' . now()->format('Y-m-d') . '.xls', ['Content-Type' => 'application/vnd.ms-excel']);
        }

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Santri', 'Kasir', 'Metode Pembayaran', 'Total', 'Waktu']);
            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->transaction_code,
                    $transaction->student?->name ?? '-',
                    $transaction->user?->name ?? '-',
                    $transaction->payment_type,
                    $transaction->total,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, 'laporan-penjualan-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function applyFilters($query, Request $request): void
    {
        $query
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('payment_type'), fn ($q) => $q->where('payment_type', $request->payment_type))
            ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('product_id'), fn ($q) => $q->whereHas('items', fn ($items) => $items->where('product_id', $request->integer('product_id'))))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('items.product', fn ($product) => $product->where('category', $request->category)));
    }
}
