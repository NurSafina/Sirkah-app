<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureProductAccess();
        $products = Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $this->ensureAdminAccess();
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:products'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['category_id'] = Category::firstOrCreate(['name' => trim($data['category'])])->id;

        $product = Product::create([
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'name' => $data['name'],
            'category' => $data['category'],
            'category_id' => $data['category_id'] ?? null,
            'unit' => $data['unit'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'cost_price' => $data['cost_price'] ?? 0,
            'stock' => $data['stock'],
            'minimum_stock' => $data['minimum_stock'],
            'is_active' => $request->boolean('is_active'),
        ]);
        ProductPrice::create(['product_id' => $product->id, 'price' => $product->price, 'effective_from' => now(), 'created_by' => auth()->id()]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'product.created', 'auditable_type' => Product::class, 'auditable_id' => $product->id, 'new_values' => $product->only(['name', 'price', 'stock']), 'ip_address' => $request->ip()]);

        return redirect()->route('stock.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureAdminAccess();
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['category_id'] = Category::firstOrCreate(['name' => trim($data['category'])])->id;

        $oldPrice = (float) $product->price;
        $product->update([
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'name' => $data['name'],
            'category' => $data['category'],
            'category_id' => $data['category_id'] ?? null,
            'unit' => $data['unit'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'cost_price' => $data['cost_price'] ?? 0,
            'stock' => $data['stock'],
            'minimum_stock' => $data['minimum_stock'],
            'is_active' => $request->boolean('is_active'),
        ]);
        if ($oldPrice !== (float) $product->price) {
            ProductPrice::where('product_id', $product->id)->whereNull('effective_until')->update(['effective_until' => now()]);
            ProductPrice::create(['product_id' => $product->id, 'price' => $product->price, 'effective_from' => now(), 'created_by' => auth()->id()]);
        }
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'product.updated', 'auditable_type' => Product::class, 'auditable_id' => $product->id, 'new_values' => $product->only(['name', 'price', 'stock']), 'ip_address' => $request->ip()]);

        return redirect()->route('stock.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->ensureAdminAccess();

        if ($product->transactionItems()->exists()) {
            return back()->withErrors([
                'product' => 'Produk ' . $product->name . ' tidak dapat dihapus karena sudah memiliki riwayat transaksi.',
            ]);
        }

        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function ensureProductAccess(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && ($user->isAdmin() || $user->isCashier()), 403);
    }

    private function ensureAdminAccess(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isAdmin(), 403);
    }
}
