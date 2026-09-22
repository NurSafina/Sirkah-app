@extends('layouts.app')

@section('title', 'Stok Harian')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">OPERASIONAL KANTIN</p>
            <h1>Stok Harian</h1>
            <p class="muted">Catat pergerakan dan sisa stok setiap produk dalam satu laporan.</p>
        </div>
        <div class="date-chip">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>

    <div class="stock-layout">
        <div class="card stock-form-card">
            <div class="stock-card-heading">
                <div class="section-icon" aria-hidden="true">▦</div>
                <div>
                    <h2>{{ $editingReport ? 'Edit laporan stok' : 'Input stok hari ini' }}</h2>
                    <p class="muted">Angka sisa akan menjadi stok aktif produk.</p>
                </div>
            </div>

            <form method="POST" action="{{ $editingReport ? route('stock.update', $editingReport) : route('stock.store') }}">
                @csrf
                @if ($editingReport)
                    @method('PUT')
                @endif
                <div class="form-grid">
                    <div>
                        <label for="report_date">Tanggal laporan</label>
                        <input id="report_date" type="date" name="report_date" value="{{ old('report_date', $editingReport?->report_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" @readonly($editingReport) required>
                    </div>
                    <div>
                        <label for="product_id">Produk</label>
                        <select id="product_id" name="product_id" @disabled($editingReport) required>
                            <option value="">Pilih produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $editingReport?->product_id) == $product->id)>
                                    {{ $product->name }} (stok saat ini: {{ $product->stock }} {{ $product->unit }})
                                </option>
                            @endforeach
                        </select>
                        @if ($editingReport)
                            <input type="hidden" name="product_id" value="{{ $editingReport->product_id }}">
                        @endif
                    </div>
                </div>

                <div class="stock-number-grid">
                    <div>
                        <label for="stock_in">Stok masuk</label>
                        <input id="stock_in" type="number" name="stock_in" value="{{ old('stock_in', $editingReport?->stock_in ?? 0) }}" min="0" required>
                        <small class="form-help">Barang baru datang</small>
                    </div>
                    <div>
                        <label for="stock_out">Stok keluar</label>
                        <input id="stock_out" type="number" name="stock_out" value="{{ old('stock_out', $editingReport?->stock_out ?? 0) }}" min="0" required>
                        <small class="form-help">Dipindahkan atau dikeluarkan</small>
                    </div>
                    <div>
                        <label for="source">Sumber/supplier</label>
                        <input id="source" type="text" name="source" value="{{ old('source', $editingReport?->source) }}" placeholder="Nama pemasok">
                    </div>
                    <div>
                        <label for="invoice_number">Nomor nota</label>
                        <input id="invoice_number" type="text" name="invoice_number" value="{{ old('invoice_number', $editingReport?->invoice_number) }}">
                    </div>
                    <div>
                        <label for="cost_price">Harga modal</label>
                        <input id="cost_price" type="number" name="cost_price" value="{{ old('cost_price', $editingReport?->cost_price) }}" min="0" step="100">
                    </div>
                    <div>
                        <label for="sold">Barang terjual</label>
                        <input id="sold" type="number" name="sold" value="{{ old('sold', $editingReport?->sold ?? 0) }}" min="0" required>
                        <small class="form-help">Total penjualan hari ini</small>
                    </div>
                    <div>
                        <label for="damaged">Barang rusak</label>
                        <input id="damaged" type="number" name="damaged" value="{{ old('damaged', $editingReport?->damaged ?? 0) }}" min="0" required>
                        <small class="form-help">Tidak dapat dijual</small>
                    </div>
                    <div class="remaining-field">
                        <label for="remaining">Sisa stok</label>
                        <input id="remaining" type="number" name="remaining" value="{{ old('remaining', $editingReport?->remaining ?? 0) }}" min="0" required>
                        <small class="form-help">Hasil hitung fisik terakhir</small>
                    </div>
                </div>

                <label for="notes">Catatan <span class="muted">(opsional)</span></label>
                <textarea id="notes" name="notes" rows="3" placeholder="Contoh: stok diterima dari pemasok pagi ini.">{{ old('notes', $editingReport?->notes) }}</textarea>

                <button class="btn btn-primary" type="submit">{{ $editingReport ? 'Update laporan stok' : 'Simpan laporan stok' }}</button>
                @if ($editingReport)
                    <a class="btn btn-secondary" href="{{ route('stock.index') }}">Batal</a>
                @endif
            </form>
        </div>

        <div class="card stock-history-card">
            <div class="card-heading">
                <div>
                    <h2>Riwayat laporan</h2>
                    <p class="muted">30 laporan stok terakhir</p>
                </div>
            </div>
            <div class="stock-history-list">
                @forelse ($reports as $report)
                    <article class="stock-history-item">
                        <div class="stock-history-title">
                            <strong>{{ $report->product->name }}</strong>
                            <span>{{ $report->report_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="stock-history-values">
                            <span><b>+{{ $report->stock_in }}</b> masuk</span>
                            <span><b>{{ $report->stock_out }}</b> keluar</span>
                            <span><b>{{ $report->sold }}</b> terjual</span>
                            <span><b>{{ $report->damaged }}</b> rusak</span>
                            <span class="stock-remaining"><b>{{ $report->remaining }}</b> sisa</span>
                        </div>
                        <div class="stock-history-footer">
                            <small class="muted">Diisi oleh {{ $report->user->name ?? 'Pengguna' }}</small>
                            @if (auth()->user()->isAdmin())
                                <a class="btn btn-secondary btn-small" href="{{ route('stock.index', ['edit' => $report->id]) }}">Edit</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Belum ada laporan stok harian.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card product-management-card">
        <div class="card-heading">
            <div>
                <h2>Kelola barang</h2>
                <p class="muted">Admin mengelola barang; kasir mencatat stok masuk sesuai kewenangan.</p>
            </div>
        </div>

        @if (auth()->user()->isAdmin())
        <form class="product-create-form" method="POST" action="{{ $editingProduct ? route('products.update', $editingProduct) : route('products.store') }}">
            @csrf
            <input type="hidden" name="is_active" value="1">
            @if ($editingProduct)
                @method('PUT')
            @endif
            <div>
                <label for="product_sku">SKU</label>
                <input id="product_sku" type="text" name="sku" value="{{ old('sku', $editingProduct?->sku) }}" placeholder="Contoh: MKN-001" required>
            </div>
            <div>
                <label for="product_barcode">Barcode barang</label>
                <input id="product_barcode" type="text" name="barcode" value="{{ old('barcode', $editingProduct?->barcode) }}" placeholder="Scan atau ketik kode barcode">
                <small class="form-help">Kode harus unik. Scanner biasanya mengisi kolom ini otomatis.</small>
            </div>
            <div>
                <label for="product_name">Nama barang</label>
                <input id="product_name" type="text" name="name" value="{{ old('name', $editingProduct?->name) }}" placeholder="Nama produk" required>
            </div>
            <div>
                <label for="product_category">Kategori</label>
                <input id="product_category" type="text" name="category" value="{{ old('category', $editingProduct?->category) }}" placeholder="Makanan" required>
            </div>
            <div>
                <label for="product_unit">Satuan</label>
                <input id="product_unit" type="text" name="unit" value="{{ old('unit', $editingProduct?->unit ?? 'pcs') }}" required>
            </div>
            <div>
                <label for="product_price">Harga jual</label>
                <input id="product_price" type="number" name="price" value="{{ old('price', $editingProduct?->price) }}" min="0" step="100" required>
            </div>
            <div>
                <label for="product_cost_price">Harga modal</label>
                <input id="product_cost_price" type="number" name="cost_price" value="{{ old('cost_price', $editingProduct?->cost_price ?? 0) }}" min="0" step="100">
            </div>
            <div>
                <label for="product_stock">Stok awal</label>
                <input id="product_stock" type="number" name="stock" value="{{ old('stock', $editingProduct?->stock ?? 0) }}" min="0" required>
            </div>
            <div>
                <label for="product_minimum_stock">Stok minimum</label>
                <input id="product_minimum_stock" type="number" name="minimum_stock" value="{{ old('minimum_stock', $editingProduct?->minimum_stock ?? 5) }}" min="0" required>
            </div>
            <div class="product-create-action">
                <button class="btn btn-primary" type="submit">{{ $editingProduct ? 'Update barang' : 'Tambah barang' }}</button>
                @if ($editingProduct)
                    <a class="btn btn-secondary" href="{{ route('stock.index') }}">Batal</a>
                @endif
            </div>
        </form>

        <div class="product-management-table">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Harga jual</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong><br><small class="muted">{{ $product->sku }}</small></td>
                            <td>{{ $product->category }}</td>
                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td>{{ $product->stock }} {{ $product->unit }}</td>
                            <td>
                                <div class="product-row-actions">
                                    <a class="btn btn-secondary btn-small" href="{{ route('stock.index', ['edit_product' => $product->id]) }}">Edit</a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm(@js('Hapus produk ' . $product->name . '?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-small" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada barang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="card">
        <h2>Penyesuaian stok</h2>
        <p class="muted">Gunakan angka positif untuk menambah dan angka negatif untuk mengurangi stok. Alasan wajib diisi.</p>
        <form method="POST" action="{{ route('stock.adjust') }}" class="form-grid">
            @csrf
            <div><label for="adjust_product_id">Produk</label><select id="adjust_product_id" name="product_id" required><option value="">Pilih produk</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->stock }})</option>@endforeach</select></div>
            <div><label for="adjust_quantity">Perubahan jumlah</label><input id="adjust_quantity" type="number" name="quantity" placeholder="Contoh: -2 atau 5" required></div>
            <div><label for="adjust_reason">Alasan</label><input id="adjust_reason" type="text" name="reason" placeholder="Rusak, hilang, kedaluwarsa, atau koreksi" required></div>
            <div><button class="btn btn-primary" type="submit">Simpan penyesuaian</button></div>
        </form>
    </div>
@endsection
