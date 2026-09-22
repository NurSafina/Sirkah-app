<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        @page { margin: 18px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #172033; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        p { margin: 0 0 14px; color: #667085; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #b9c2d0; padding: 7px; text-align: left; }
        th { background: #0f2747; color: #fff; }
        td.amount { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <p>{{ config('app.name') }} · Periode: {{ $dateFrom ?: 'awal' }} sampai {{ $dateTo ?: 'hari ini' }}</p>
    <table>
        <thead><tr><th>Kode</th><th>Santri</th><th>Kasir</th><th>Metode</th><th>Total</th><th>Waktu</th></tr></thead>
        <tbody>
        @forelse($transactions as $transaction)
            <tr>
                <td>{{ $transaction->transaction_code }}</td>
                <td>{{ $transaction->student?->name ?? '-' }}</td>
                <td>{{ $transaction->user?->name ?? '-' }}</td>
                <td>{{ strtoupper($transaction->payment_type) }}</td>
                <td class="amount">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                <td>{{ optional($transaction->created_at)->format('d-m-Y H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Belum ada transaksi pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
