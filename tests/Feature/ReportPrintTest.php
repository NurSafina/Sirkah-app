<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_printable_report_with_stock_revenue_and_student_spending(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-report',
            'role' => 'admin',
        ]);

        $student = Student::create([
            'student_number' => 'S-002',
            'name' => 'Adit',
            'classroom' => 'X-B',
            'status' => 'active',
            'balance' => 200000,
            'daily_limit' => 50000,
        ]);

        $product = Product::create([
            'sku' => 'SKU-001',
            'barcode' => '123',
            'name' => 'Nasi Goreng',
            'category' => 'Makanan',
            'unit' => 'porsi',
            'description' => 'Makanan utama',
            'price' => 12000,
            'cost_price' => 7000,
            'stock' => 15,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'student_id' => $student->id,
            'user_id' => $admin->id,
            'transaction_code' => 'TRX-PRINT-1',
            'total' => 12000,
            'payment_type' => 'saldo',
            'status' => 'completed',
            'notes' => 'Uji laporan',
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 1,
            'subtotal' => 12000,
        ]);

        $response = $this->actingAs($admin)->get('/reports');

        $response->assertOk();
        $response->assertSee('Cetak Laporan');
        $response->assertSee('Stok Barang');
        $response->assertSee('Pendapatan');
        $response->assertSee('Pengeluaran');
        $response->assertSee('Jajan Siswa per Hari');
    }
}
