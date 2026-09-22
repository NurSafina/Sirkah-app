@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-topbar">
        <div>
            <p class="breadcrumb">Home <span>/</span> Dashboard</p>
            <h1>Dashboard</h1>
        </div>
        <details class="user-menu-dropdown">
            <summary class="user-chip user-menu-button" title="Buka pilihan akun">
                <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
            </summary>
            <div class="user-menu-options">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}">Kelola Akun Kasir</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </details>
    </div>

    <div class="dashboard-welcome">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong><span>Berikut ringkasan operasional kantin hari ini.</span></div>

    <div class="dashboard-section-heading stats-heading">
        <div>
            <h2>Overview</h2>
            <p class="muted">Ringkasan data kantin secara keseluruhan.</p>
        </div>
    </div>

    <div class="dashboard-stats">
        <div class="stat stat-blue"><div class="stat-icon">♙</div><span>Total Siswa</span><strong>{{ $stats['students'] }}</strong><small>Data siswa aktif</small></div>
        <div class="stat stat-green"><div class="stat-icon">▦</div><span>Total Produk</span><strong>{{ $stats['products'] }}</strong><small>Produk tersedia</small></div>
        <div class="stat stat-coral"><div class="stat-icon">↗</div><span>Pendapatan</span><strong>Rp {{ number_format($stats['income'], 0, ',', '.') }}</strong><small>Transaksi selesai</small></div>
        <div class="stat stat-orange"><div class="stat-icon">!</div><span>Stok Rendah</span><strong>{{ $stats['low_stock'] }}</strong><small>Perlu diperiksa</small></div>
    </div>

    <div class="dashboard-section-heading feature-heading">
        <div>
            <h2>Menu utama</h2>
            <p class="muted">Akses cepat ke fitur pengelolaan kantin.</p>
        </div>
    </div>

    <div class="feature-grid dashboard-features">
        <a class="feature-box feature-box-primary" href="{{ route('pos.index') }}">
            <span class="feature-icon">＋</span>
            <span class="feature-content"><strong>Mulai Transaksi</strong><small>Proses pembelian siswa</small></span>
            <span class="feature-arrow">→</span>
        </a>
        <a class="feature-box" href="{{ route('transactions.index') }}">
            <span class="feature-icon">↔</span>
            <span class="feature-content"><strong>Transaksi</strong><small>Lihat riwayat transaksi</small></span>
            <span class="feature-arrow">→</span>
        </a>
        @if (auth()->user()->isAdmin())
            <a class="feature-box" href="{{ route('students.index') }}">
                <span class="feature-icon">♙</span>
                <span class="feature-content"><strong>Kelola Siswa</strong><small>Data dan saldo siswa</small></span>
                <span class="feature-arrow">→</span>
            </a>
            <a class="feature-box" href="{{ route('products.index') }}">
                <span class="feature-icon">▦</span>
                <span class="feature-content"><strong>Kelola Produk</strong><small>Harga, kategori, dan stok</small></span>
                <span class="feature-arrow">→</span>
            </a>
            <a class="feature-box" href="{{ route('reports.index') }}">
                <span class="feature-icon">▤</span>
                <span class="feature-content"><strong>Laporan</strong><small>Ringkasan kinerja kantin</small></span>
                <span class="feature-arrow">→</span>
            </a>
        @endif
    </div>

    <div class="card latest-card">
        <div class="card-heading"><div><h2>Transaksi Terbaru</h2><p class="muted">Aktivitas transaksi terakhir</p></div><a href="{{ route('transactions.index') }}">Lihat semua →</a></div>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Siswa</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($latestTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_code }}</td>
                        <td>{{ $transaction->student?->name ?? '-' }}</td>
                        <td>{{ $transaction->user?->name ?? '-' }}</td>
                        <td>Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                        <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
