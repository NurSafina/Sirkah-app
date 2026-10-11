<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_name_is_trimmed_before_unique_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'category-admin-1']);
        Category::create(['name' => 'Makanan']);

        $this->actingAs($admin)
            ->from('/products')
            ->post('/categories', ['name' => ' Makanan '])
            ->assertRedirect('/products')
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_updating_category_trims_name_and_updates_related_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'category-admin-2']);
        $category = Category::create(['name' => 'Makanan']);
        $product = Product::create([
            'sku' => 'CATEGORY-001',
            'name' => 'Produk Uji',
            'category' => 'Makanan',
            'category_id' => $category->id,
            'unit' => 'pcs',
            'price' => 5000,
            'cost_price' => 3000,
            'stock' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put('/categories/' . $category->id, ['name' => '  Minuman  '])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Minuman']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category' => 'Minuman']);
    }
}
