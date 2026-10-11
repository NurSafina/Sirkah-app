<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_update_a_product(): void
    {
        $cashier = User::factory()->create(['username' => 'cashier-product-update', 'role' => 'cashier']);
        $product = Product::create([
            'sku' => 'PROD-001', 'name' => 'Air Mineral', 'category' => 'Minuman',
            'unit' => 'botol', 'price' => 3000, 'cost_price' => 1500,
            'stock' => 10, 'minimum_stock' => 2, 'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->put(route('products.update', $product), [
            'sku' => 'PROD-001', 'name' => 'Air Mineral Besar', 'category' => 'Minuman',
            'unit' => 'botol', 'price' => 4000, 'cost_price' => 2000,
            'stock' => 8, 'minimum_stock' => 2, 'is_active' => 1,
        ]);

        $response->assertRedirect(route('stock.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Air Mineral Besar', 'price' => 4000, 'stock' => 8]);
    }

    public function test_cashier_can_delete_a_product_without_transaction_history(): void
    {
        $cashier = User::factory()->create(['username' => 'cashier-product-delete', 'role' => 'cashier']);
        $product = Product::create([
            'sku' => 'PROD-002', 'name' => 'Roti', 'category' => 'Makanan',
            'unit' => 'pcs', 'price' => 5000, 'cost_price' => 2500,
            'stock' => 5, 'minimum_stock' => 1, 'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->delete(route('products.destroy', $product));

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
