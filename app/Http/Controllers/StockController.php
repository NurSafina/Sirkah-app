<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use App\Models\Category;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStockAccess();

        $products = Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();
        $reports = StockReport::with(['product', 'user'])
            ->latest('report_date')
            ->latest()
            ->limit(30)
            ->get();
        $editingReport = $request->filled('edit')
            ? StockReport::with('product')->findOrFail($request->integer('edit'))
            : null;
        $editingProduct = $request->filled('edit_product')
            ? Product::findOrFail($request->integer('edit_product'))
            : null;

        return view('stock.index', compact('products', 'categories', 'reports', 'editingReport', 'editingProduct'));
    }

    public function store(Request $request)
    {
        $this->ensureStockAccess();

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'report_date' => ['required', 'date'],
            'stock_in' => ['required', 'integer', 'min:0'],
            'source' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_out' => ['required', 'integer', 'min:0'],
            'sold' => ['required', 'integer', 'min:0'],
            'damaged' => ['required', 'integer', 'min:0'],
            'remaining' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $report = StockReport::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'report_date' => $data['report_date'],
                ],
                [
                    'user_id' => auth()->id(),
                    'stock_in' => $data['stock_in'],
                    'source' => $data['source'] ?? null,
                    'invoice_number' => $data['invoice_number'] ?? null,
                    'cost_price' => $data['cost_price'] ?? null,
                    'stock_out' => $data['stock_out'],
                    'sold' => $data['sold'],
                    'damaged' => $data['damaged'],
                    'remaining' => $data['remaining'],
                    'notes' => $data['notes'] ?? null,
                ]
            );

            StockMovement::where('reference_type', 'stock_report')
                ->where('reference_id', $report->id)
                ->delete();

            $movements = [
                ['type' => 'in', 'quantity' => $data['stock_in'], 'reason' => 'Stok masuk harian'],
                ['type' => 'out', 'quantity' => $data['stock_out'], 'reason' => 'Barang keluar harian'],
                ['type' => 'adjustment', 'quantity' => $data['damaged'], 'reason' => 'Barang rusak'],
            ];

            foreach ($movements as $movement) {
                if ($movement['quantity'] === 0) {
                    continue;
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => $movement['type'],
                    'quantity' => $movement['quantity'],
                    'reason' => $movement['reason'],
                    'reference_type' => 'stock_report',
                    'reference_id' => $report->id,
                ]);
            }

            $product->update(['stock' => $data['remaining']]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => 'stock.report_saved', 'auditable_type' => StockReport::class, 'auditable_id' => $report->id, 'new_values' => ['product_id' => $product->id, 'stock_in' => $data['stock_in'], 'remaining' => $data['remaining']], 'ip_address' => request()->ip()]);
        });

        return redirect()->route('stock.index')->with('success', 'Laporan stok harian berhasil disimpan.');
    }

    public function update(Request $request, StockReport $stockReport)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->ensureStockAccess();

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'report_date' => ['required', 'date'],
            'stock_in' => ['required', 'integer', 'min:0'],
            'source' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_out' => ['required', 'integer', 'min:0'],
            'sold' => ['required', 'integer', 'min:0'],
            'damaged' => ['required', 'integer', 'min:0'],
            'remaining' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $stockReport) {
            $product = Product::whereKey($stockReport->product_id)->lockForUpdate()->firstOrFail();

            $stockReport->update([
                'user_id' => auth()->id(),
                'stock_in' => $data['stock_in'],
                'source' => $data['source'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'cost_price' => $data['cost_price'] ?? null,
                'stock_out' => $data['stock_out'],
                'sold' => $data['sold'],
                'damaged' => $data['damaged'],
                'remaining' => $data['remaining'],
                'notes' => $data['notes'] ?? null,
            ]);

            StockMovement::where('reference_type', 'stock_report')
                ->where('reference_id', $stockReport->id)
                ->delete();

            $movements = [
                ['type' => 'in', 'quantity' => $data['stock_in'], 'reason' => 'Stok masuk harian'],
                ['type' => 'out', 'quantity' => $data['stock_out'], 'reason' => 'Barang keluar harian'],
                ['type' => 'adjustment', 'quantity' => $data['damaged'], 'reason' => 'Barang rusak'],
            ];

            foreach ($movements as $movement) {
                if ($movement['quantity'] === 0) {
                    continue;
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => $movement['type'],
                    'quantity' => $movement['quantity'],
                    'reason' => $movement['reason'],
                    'reference_type' => 'stock_report',
                    'reference_id' => $stockReport->id,
                ]);
            }

            $product->update(['stock' => $data['remaining']]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => 'stock.report_updated', 'auditable_type' => StockReport::class, 'auditable_id' => $stockReport->id, 'new_values' => ['product_id' => $product->id, 'remaining' => $data['remaining']], 'ip_address' => request()->ip()]);
        });

        return redirect()->route('stock.index')->with('success', 'Laporan stok harian berhasil diperbarui.');
    }

    public function adjust(Request $request)
    {
        $this->ensureStockAccess();
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $before = (int) $product->stock;
            $after = $before + (int) $data['quantity'];
            abort_if($after < 0, 422, 'Stok tidak boleh menjadi negatif.');
            $product->update(['stock' => $after]);
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity' => abs((int) $data['quantity']),
                'stock_before' => $before,
                'stock_after' => $after,
                'reason' => $data['reason'],
                'reference_type' => 'manual_adjustment',
            ]);
            AuditLog::create([
                'user_id' => auth()->id(), 'action' => 'stock.adjusted',
                'auditable_type' => Product::class, 'auditable_id' => $product->id,
                'new_values' => ['quantity' => $data['quantity'], 'reason' => $data['reason'], 'stock_before' => $before, 'stock_after' => $after],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    private function ensureStockAccess(): void
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isCashier(), 403);
    }
}
