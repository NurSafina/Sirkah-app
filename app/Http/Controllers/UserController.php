<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $editingUser = $request->filled('edit') ? User::findOrFail($request->integer('edit')) : null;
        $users = User::where('role', 'cashier')->latest()->get();
        return view('users.index', compact('users', 'editingUser'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'cashier';
        $data['is_active'] = true;
        $user = User::create($data);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'user.created', 'auditable_type' => User::class, 'auditable_id' => $user->id, 'new_values' => ['username' => $user->username, 'role' => $user->role], 'ip_address' => $request->ip()]);
        return redirect()->route('users.index')->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->isCashier(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        if (blank($data['password'] ?? null)) unset($data['password']); else $data['password'] = Hash::make($data['password']);
        $user->update($data);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'user.updated', 'auditable_type' => User::class, 'auditable_id' => $user->id, 'new_values' => ['username' => $user->username], 'ip_address' => $request->ip()]);
        return redirect()->route('users.index')->with('success', 'Akun kasir berhasil diperbarui.');
    }

    public function toggle(User $user)
    {
        abort_unless($user->isCashier(), 404);
        $user->update(['is_active' => ! $user->is_active]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'user.status_changed', 'auditable_type' => User::class, 'auditable_id' => $user->id, 'new_values' => ['is_active' => $user->is_active], 'ip_address' => request()->ip()]);
        return back()->with('success', 'Status akun kasir berhasil diperbarui.');
    }
}
