<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => Student::count(),
            'products' => Product::count(),
            'income' => (float) Transaction::where('status', 'completed')->sum('total'),
            'low_stock' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
        ];

        $latestTransactions = Transaction::with(['student', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'latestTransactions'));
    }
}
