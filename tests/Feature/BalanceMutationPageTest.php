<?php

namespace Tests\Feature;

use App\Models\BalanceMutation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceMutationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_balance_mutation_history(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-mutasi',
            'role' => 'admin',
        ]);

        $student = Student::create([
            'student_number' => 'S-001',
            'name' => 'Rina',
            'classroom' => 'XI-A',
            'status' => 'active',
            'balance' => 50000,
            'daily_limit' => 20000,
        ]);

        BalanceMutation::create([
            'student_id' => $student->id,
            'user_id' => $admin->id,
            'type' => 'topup',
            'amount' => 50000,
            'description' => 'Top-up saldo siswa',
            'reference_type' => 'student_topup',
            'reference_id' => $student->id,
        ]);

        $response = $this->actingAs($admin)->get('/balance-mutations');

        $response->assertOk();
        $response->assertSee('Mutasi Saldo');
        $response->assertSee('Rina');
        $response->assertSee('Top-up saldo siswa');
    }
}
