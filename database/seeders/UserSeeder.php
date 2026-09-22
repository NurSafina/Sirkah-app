<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@kantin.local',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir 1',
                'email' => 'kasir@kantin.local',
                'password' => Hash::make('password123'),
                'role' => 'cashier',
            ]
        );

        Student::updateOrCreate(
            ['student_number' => 'S-1001'],
            [
                'name' => 'Andi Wijaya',
                'classroom' => '7A',
                'status' => 'active',
                'balance' => 25000,
                'daily_limit' => 30000,
                'notes' => 'Siswa aktif',
            ]
        );

        Student::updateOrCreate(
            ['student_number' => 'S-1002'],
            [
                'name' => 'Siti Nurhaliza',
                'classroom' => '8B',
                'status' => 'active',
                'balance' => 18000,
                'daily_limit' => 30000,
                'notes' => 'Siswa aktif',
            ]
        );

        Product::updateOrCreate(
            ['sku' => 'P-001'],
            [
                'name' => 'Nasi Uduk',
                'category' => 'Makanan',
                'unit' => 'porsi',
                'barcode' => '899100100001',
                'description' => 'Nasi uduk dengan ayam',
                'price' => 12000,
                'cost_price' => 8000,
                'stock' => 25,
                'minimum_stock' => 5,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['sku' => 'P-002'],
            [
                'name' => 'Es Teh',
                'category' => 'Minuman',
                'unit' => 'gelas',
                'barcode' => '899100100002',
                'description' => 'Es teh manis dingin',
                'price' => 4000,
                'cost_price' => 1500,
                'stock' => 40,
                'minimum_stock' => 5,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['sku' => 'P-003'],
            [
                'name' => 'Bakso',
                'category' => 'Makanan',
                'unit' => 'porsi',
                'barcode' => '899100100003',
                'description' => 'Bakso dengan kuah',
                'price' => 15000,
                'cost_price' => 10000,
                'stock' => 18,
                'minimum_stock' => 5,
                'is_active' => true,
            ]
        );
    }
}
