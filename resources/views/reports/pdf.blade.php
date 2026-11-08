<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kantin</title>
    <style>
        @page { margin: 18px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #172033; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        h2 { margin: 18px 0 6px; font-size: 13px; }
        p { margin: 0 0 14px; color: #667085; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #b9c2d0; padding: 7px; text-align: left; }
        th { background: #0f2747; color: #fff; }
        td.amount { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Kantin</h1>
    <p>{{ config('app.name') }} · Periode: {{ $dateFrom ?: 'awal' }} sampai {{ $dateTo ?: 'hari ini' }}</p>
    <h2>Ringkasan Laporan</h2>
    <table>
        <thead><tr><th>Transaksi Selesai</th><th>Pendapatan</th><th>Modal Terjual</th><th>Keuntungan</th><th>Total Top-up</th></tr></thead>
        <tbody><tr><td>{{ $transactions->count() }}</td><td>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td><td>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td><td>Rp {{ number_format($profit, 0, ',', '.') }}</td><td>Rp {{ number_format($totalTopups, 0, ',', '.') }}</td></tr></tbody>
    </table>
    <h2>Daftar Transaksi Penjualan</h2>
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
    <h2>Stok Barang</h2>
    <table><thead><tr><th>Barang</th><th>Kategori</th><th>Stok</th><th>Harga Jual</th><th>Harga Modal</th></tr></thead><tbody>
        @forelse($stockSummary as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->category ?: '-' }}</td><td>{{ $item->stock }}</td><td>Rp {{ number_format($item->price, 0, ',', '.') }}</td><td>Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="5">Belum ada data stok.</td></tr>@endforelse
    </tbody></table>
    <h2>Produk Terlaris</h2>
    <table><thead><tr><th>Produk</th><th>Jumlah Terjual</th><th>Pendapatan</th></tr></thead><tbody>
        @forelse($topProducts as $row)<tr><td>{{ $row->product_name }}</td><td>{{ $row->sold }}</td><td>Rp {{ number_format($row->revenue, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="3">Belum ada data penjualan.</td></tr>@endforelse
    </tbody></table>
    <h2>Pendapatan Harian</h2>
    <table><thead><tr><th>Tanggal</th><th>Total</th></tr></thead><tbody>
        @forelse($dailyRevenue as $row)<tr><td>{{ $row->date }}</td><td>Rp {{ number_format($row->total, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="2">Belum ada laporan.</td></tr>@endforelse
    </tbody></table>
    <h2>Jajan Santri per Hari</h2>
    <table><thead><tr><th>Tanggal</th><th>Total Jajan</th></tr></thead><tbody>
        @forelse($dailyStudentSpending as $row)<tr><td>{{ $row->date }}</td><td>Rp {{ number_format($row->total_spent, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="2">Belum ada data jajan santri.</td></tr>@endforelse
    </tbody></table>
    <h2>Saldo Santri</h2>
    <table><thead><tr><th>Santri</th><th>Saldo</th></tr></thead><tbody>
        @forelse($studentBalances as $student)<tr><td>{{ $student->name }}</td><td>Rp {{ number_format($student->balance, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="2">Belum ada data saldo.</td></tr>@endforelse
    </tbody></table>
</body>
</html>
