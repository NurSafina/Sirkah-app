@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="card">
        <div class="card-heading">
            <div>
                <h2>Daftar Produk</h2>
                <p class="muted">Produk yang sudah diinput melalui Stok Harian.</p>
            </div>
        </div>
        <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Barcode</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->name }}<br><small class="muted">{{ $product->sku }}</small></td>
                            <td>{{ $product->category }}</td>
                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td>{{ $product->barcode ?: '-' }}</td>
                            <td>{{ $product->stock }} {{ $product->unit }}</td>
                            <td>
                                @if (auth()->user()->isAdmin())
                                    <a class="btn btn-secondary btn-small" href="{{ route('stock.index', ['edit_product' => $product->id]) }}">Edit</a>
                                @else
                                    <span class="muted">Lihat saja</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
        </table>
    </div>
@endsection
