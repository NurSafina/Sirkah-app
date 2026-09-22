<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SirkahCoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_sell_using_an_active_student_card(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'username' => 'kasir-flow-1']);
        $student = Student::create([
            'student_number' => 'S-2001', 'name' => 'Santri Uji', 'classroom' => 'A',
            'card_token' => 'card-test-2001', 'card_status' => 'active', 'status' => 'active',
            'balance' => 20000, 'daily_limit' => 15000,
        ]);
        $product = Product::create([
            'sku' => 'TEST-001', 'name' => 'Produk Uji', 'category' => 'Uji', 'unit' => 'pcs',
            'price' => 5000, 'cost_price' => 3000, 'stock' => 10, 'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post('/pos/checkout', [
            'student_id' => $student->id,
            'items' => [$product->id => 2],
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', ['student_id' => $student->id, 'user_id' => $cashier->id, 'status' => 'completed']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'balance' => 10000]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
    }

    public function test_inactive_card_is_rejected(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'username' => 'kasir-flow-2']);
        $student = Student::create([
            'student_number' => 'S-2002', 'name' => 'Kartu Nonaktif', 'classroom' => 'A',
            'card_token' => 'card-test-2002', 'card_status' => 'inactive', 'status' => 'active',
            'balance' => 20000, 'daily_limit' => 0,
        ]);
        $product = Product::create([
            'sku' => 'TEST-002', 'name' => 'Produk Uji 2', 'category' => 'Uji', 'unit' => 'pcs',
            'price' => 5000, 'cost_price' => 3000, 'stock' => 10, 'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)->post('/pos/checkout', ['student_id' => $student->id, 'items' => [$product->id => 1]])
            ->assertStatus(422);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_cashier_cannot_change_products_or_cancel_transactions(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'username' => 'kasir-flow-3']);
        $product = Product::create([
            'sku' => 'TEST-003', 'name' => 'Produk Uji 3', 'category' => 'Uji', 'unit' => 'pcs',
            'price' => 5000, 'cost_price' => 3000, 'stock' => 10, 'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)->put('/products/' . $product->id, [
            'sku' => 'TEST-003', 'name' => 'Diubah', 'category' => 'Uji', 'unit' => 'pcs',
            'price' => 1, 'cost_price' => 1, 'stock' => 10, 'minimum_stock' => 1,
        ])->assertForbidden();

        $transaction = Transaction::create([
            'user_id' => $cashier->id, 'transaction_code' => 'TRX-TEST-003', 'total' => 5000,
            'payment_type' => 'saldo', 'status' => 'completed',
        ]);
        $this->actingAs($cashier)->delete('/transactions/' . $transaction->id)->assertForbidden();
    }
}
