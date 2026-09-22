@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="card">
    <div class="card-heading"><div><h2>Audit Log</h2><p class="muted">Riwayat aktivitas penting sistem.</p></div></div>
    <table><thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Data</th><th>IP</th></tr></thead><tbody>
    @forelse($logs as $log)
        <tr><td>{{ $log->created_at->format('d M Y H:i:s') }}</td><td>{{ $log->user?->name ?? '-' }}</td><td>{{ $log->action }}</td><td><pre style="white-space: pre-wrap; margin: 0;">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</pre></td><td>{{ $log->ip_address ?? '-' }}</td></tr>
    @empty <tr><td colspan="5">Belum ada aktivitas.</td></tr> @endforelse
    </tbody></table>
    <div style="margin-top: 20px;">{{ $logs->links() }}</div>
</div>
@endsection
