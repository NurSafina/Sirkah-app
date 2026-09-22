<?php

namespace App\Http\Controllers;

use App\Models\BalanceMutation;
use Illuminate\Http\Request;

class BalanceMutationController extends Controller
{
    public function index()
    {
        $mutations = BalanceMutation::with(['student', 'user'])
            ->latest()
            ->get();

        return view('balance-mutations.index', compact('mutations'));
    }
}
