@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <div class="report-page">
        <div class="card report-header">
            <div class="report-header__title-wrap">
                <h3>Laporan Kantin</h3>
                <small>Dicetak: {{ now()->translatedFormat('d F Y') }}</small>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Laporan</button>
                <a class="btn btn-secondary" href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}">Ekspor PDF</a>
                <a class="btn btn-secondary" href="{{ route('reports.export', request()->query()) }}">Ekspor CSV</a>
                <a class="btn btn-secondary" href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'xls'])) }}">Ekspor Excel</a>
            </div>
        </div>

        <div class="card report-filters">
            <form method="GET" action="{{ route('reports.index') }}" class="form-grid">
                <div><label for="date_from">Dari tanggal</label><input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}"></div>
                <div><label for="date_to">Sampai tanggal</label><input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}"></div>
                <div><label for="user_id">Kasir</label><select id="user_id" name="user_id"><option value="">Semua kasir</option>@foreach($cashiers as $cashier)<option value="{{ $cashier->id }}" @selected(request('user_id') == $cashier->id)>{{ $cashier->name }}</option>@endforeach</select></div>
                <div><label for="payment_type">Metode</label><select id="payment_type" name="payment_type"><option value="">Semua metode</option><option value="saldo" @selected(request('payment_type') === 'saldo')>Saldo santri</option><option value="cash" @selected(request('payment_type') === 'cash')>Tunai</option><option value="qris" @selected(request('payment_type') === 'qris')>QRIS</option></select></div>
                <div><label for="student_id">Santri</label><select id="student_id" name="student_id"><option value="">Semua santri</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected(request('student_id') == $student->id)>{{ $student->name }} ({{ $student->student_number }})</option>@endforeach</select></div>
                <div><label for="product_id">Produk</label><select id="product_id" name="product_id"><option value="">Semua produk</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>@endforeach</select></div>
                <div><label for="category">Kategori</label><input id="category" type="text" name="category" value="{{ request('category') }}" placeholder="Contoh: Makanan"></div>
                <div><button class="btn btn-primary" type="submit">Terapkan Filter</button> <a class="btn btn-secondary" href="{{ route('reports.index') }}">Reset</a></div>
            </form>
        </div>

        <div class="report-section card">
            <h3>Ringkasan Laporan</h3>
            <table class="report-table">
                <thead>
                    <tr><th>Pendapatan</th><th>Pengeluaran</th><th>Keuntungan</th><th>Total Top-up</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($profit, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($totalTopups, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="report-section card">
            <h3>Stok Barang</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga Jual</th>
                        <th>Harga Modal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockSummary as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->stock }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada data stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-section card">
            <h3>Produk Terlaris</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->sold }}</td>
                            <td>Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Belum ada data penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-grid-two">
            <div class="report-section card">
                <h3>Pendapatan Harian</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyRevenue as $row)
                            <tr>
                                <td>{{ $row->date }}</td>
                                <td>Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Belum ada laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="report-section card">
                <h3>Jajan Siswa per Hari</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Jajan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyStudentSpending as $row)
                            <tr>
                                <td>{{ $row->date }}</td>
                                <td>Rp {{ number_format($row->total_spent, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Belum ada data jajan siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section card">
            <h3>Saldo Santri</h3>
            <table class="report-table"><thead><tr><th>Santri</th><th>Saldo</th></tr></thead><tbody>
                @foreach($studentBalances as $studentBalance)<tr><td>{{ $studentBalance->name }}</td><td>Rp {{ number_format($studentBalance->balance, 0, ',', '.') }}</td></tr>@endforeach
            </tbody></table>
        </div>
    </div>

    <style>
        .report-page {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .report-header__title-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .report-header h3 {
            margin: 0;
        }

        .report-grid-two {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .report-section {
            overflow: hidden;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #d0d5dd;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .report-table thead th {
            background: #f8f9fb;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .report-table tbody tr:nth-child(even) {
            background: #fcfcfd;
        }

        @media (max-width: 768px) {
            .report-grid-two {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .sidebar,
            .alert,
            .btn,
            .layout > aside,
            .nav {
                display: none !important;
            }

            .content {
                padding: 0 !important;
                width: 100%;
            }

            .report-page {
                gap: 12px;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #111827 !important;
                background: #fff !important;
                break-inside: avoid;
            }

            .report-header {
                border: 0 !important;
                padding: 0 0 8px !important;
            }

            .report-table th,
            .report-table td {
                font-size: 10pt;
                padding: 6px 8px;
                border: 1px solid #111827;
            }

            .report-table thead th {
                background: #f3f4f6 !important;
            }

            .report-table tbody tr {
                page-break-inside: avoid;
            }

            @page {
                size: A4 portrait;
                margin: 12mm;
            }
        }
    </style>
@endsection
