@extends('layouts.app')

@section('title', 'Akun Kasir')

@section('content')
<div class="students-layout">
    <div class="card">
        <h3>{{ $editingUser ? 'Edit Akun Kasir' : 'Buat Akun Kasir' }}</h3>
        <form method="POST" action="{{ $editingUser ? route('users.update', $editingUser) : route('users.store') }}">
            @csrf
            @if ($editingUser) @method('PUT') @endif
            <label>Nama</label><input name="name" value="{{ old('name', $editingUser?->name) }}" required>
            <label>Username</label><input name="username" value="{{ old('username', $editingUser?->username) }}" required>
            <label>Email</label><input type="email" name="email" value="{{ old('email', $editingUser?->email) }}" required>
            <label>Password {{ $editingUser ? '(kosongkan jika tidak diubah)' : '' }}</label><input type="password" name="password" {{ $editingUser ? '' : 'required' }}>
            <label>Konfirmasi Password</label><input type="password" name="password_confirmation" {{ $editingUser ? '' : 'required' }}>
            <button class="btn btn-primary" type="submit">{{ $editingUser ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
            @if ($editingUser) <a class="btn btn-secondary" href="{{ route('users.index') }}">Batal</a> @endif
        </form>
    </div>
    <div class="card">
        <h3>Daftar Kasir</h3>
        <table><thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse($users as $user)
            <tr><td>{{ $user->name }}</td><td>{{ $user->username }}</td><td>{{ $user->email }}</td><td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td><td>
                <a class="btn btn-secondary btn-small" href="{{ route('users.index', ['edit' => $user->id]) }}">Edit</a>
                <form method="POST" action="{{ route('users.toggle', $user) }}" class="inline">@csrf<button class="btn btn-secondary btn-small" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
            </td></tr>
        @empty <tr><td colspan="5">Belum ada akun kasir.</td></tr> @endforelse
        </tbody></table>
    </div>
</div>
@endsection
