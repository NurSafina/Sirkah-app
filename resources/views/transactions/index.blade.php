@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    @if ($editingTransaction)
        <div class="card">
            <div class="card-heading">
                <div>
                    <h2>Edit Transaksi</h2>
                    <p class="muted">{{ $editingTransaction->transaction_code }}. Perubahan status akan menyesuaikan saldo dan stok.</p>
                </div>
            </div>
            <form class="form-grid transaction-edit-form" method="POST" action="{{ route('transactions.update', $editingTransaction) }}">
                @csrf
                @method('PUT')
                <div>
                    <label for="transaction_student_id">Siswa</label>
                    <select id="transaction_student_id" name="student_id" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id', $editingTransaction->student_id) == $student->id)>{{ $student->name }} ({{ $student->classroom }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="transaction_status">Status</label>
                    <select id="transaction_status" name="status" required>
                        <option value="completed" @selected(old('status', $editingTransaction->status) === 'completed')>Selesai</option>
                        <option value="cancelled" @selected(old('status', $editingTransaction->status) === 'cancelled')>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label for="transaction_notes">Catatan</label>
                    <textarea id="transaction_notes" name="notes" rows="2">{{ old('notes', $editingTransaction->notes) }}</textarea>
                </div>
                <div class="transaction-edit-actions">
                    <button class="btn btn-primary" type="submit">Update Transaksi</button>
                    <a class="btn btn-secondary" href="{{ route('transactions.index') }}">Batal</a>
                </div>
            </form>
        </div>
    @endif

    <div class="card">
        <div class="card-heading">
            <div>
                <h2>Riwayat Transaksi</h2>
                <p class="muted">Transaksi baru dibuat melalui halaman POS.</p>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Siswa</th>
                    <th>Kasir</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_code }}</td>
                        <td>{{ $transaction->student?->name ?? '-' }}</td>
                        <td>{{ $transaction->user?->name ?? '-' }}</td>
                        <td>
                            @foreach($transaction->items as $item)
                                {{ $item->product_name }} ({{ $item->quantity }})<br>
                            @endforeach
                        </td>
                        <td>Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                        <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <div class="transaction-actions">
                                <a class="btn btn-secondary btn-small" href="{{ route('transactions.index', ['edit' => $transaction->id]) }}">Edit</a>
                                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm(@js('Hapus transaksi ' . $transaction->transaction_code . '? Stok dan saldo akan dikembalikan.'))">
                                    @csrf
                                    @method('DELETE')
<button class="btn btn-danger btn-small" type="submit">Hapus / Batalkan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection