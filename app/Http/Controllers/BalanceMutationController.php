<?php

namespace App\Http\Controllers;

use App\Models\BalanceMutation;
use App\Models\AuditLog;
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

    public function destroy(BalanceMutation $balanceMutation)
    {
        $snapshot = $balanceMutation->toArray();

        $balanceMutation->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'balance_mutation.deleted',
            'auditable_type' => BalanceMutation::class,
            'auditable_id' => $balanceMutation->id,
            'old_values' => $snapshot,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Mutasi saldo berhasil dihapus dari daftar. Saldo siswa tidak diubah.');
    }
}
