@extends('layouts.app')

@section('title', 'Notifikasi WhatsApp')

@section('content')
<div class="card">
    <div class="card-heading"><div><h2>Notifikasi WhatsApp</h2><p class="muted">Status pesan transaksi dan top-up kepada orang tua/wali.</p></div></div>
    <table><thead><tr><th>Waktu</th><th>Santri</th><th>Nomor</th><th>Jenis</th><th>Status</th><th>Pesan/Error</th><th>Aksi</th></tr></thead><tbody>
    @forelse($notifications as $notification)
        <tr><td>{{ $notification->created_at->format('d/m/Y H:i') }}</td><td>{{ $notification->student?->name ?? 'Siswa sudah dihapus' }}</td><td>{{ $notification->phone_number }}</td><td>{{ $notification->transaction_id ? 'Transaksi' : 'Top-up' }}</td><td>{{ ucfirst($notification->status) }}</td><td>{{ $notification->error_message ?: 'Terkirim/menunggu proses' }}</td><td>@if($notification->status !== 'sent')<form method="POST" action="{{ route('whatsapp-notifications.retry', $notification) }}">@csrf<button class="btn btn-secondary btn-small" type="submit">Kirim Ulang</button></form>@endif</td></tr>
    @empty <tr><td colspan="7">Belum ada notifikasi.</td></tr> @endforelse
    </tbody></table>
    <div style="margin-top: 20px;">{{ $notifications->links() }}</div>
</div>
@endsection
