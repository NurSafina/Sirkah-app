@extends('layouts.app')

@section('title', 'Mutasi Saldo')

@section('content')
    <div class="card">
        <h3>Mutasi Saldo</h3>

        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Siswa</th>
                    <th>Jenis</th>
                    <th>Nominal</th>
                    <th>Deskripsi</th>
                    <th>Petugas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $mutation)
                    <tr>
                        <td>{{ $mutation->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $mutation->student?->name ?? '-' }}</td>
                        <td>
                            @php
                                $typeLabels = [
                                    'topup' => 'Top Up',
                                    'purchase' => 'Pembelian',
                                    'refund' => 'Refund',
                                ];
                            @endphp
                            {{ $typeLabels[$mutation->type] ?? ucfirst($mutation->type) }}
                        </td>
                        <td>
                            @if ((float) $mutation->amount >= 0)
                                Rp {{ number_format($mutation->amount, 0, ',', '.') }}
                            @else
                                -Rp {{ number_format(abs((float) $mutation->amount), 0, ',', '.') }}
                            @endif
                        </td>
                        <td>{{ $mutation->description }}</td>
                        <td>{{ $mutation->user?->name ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('balance-mutations.destroy', $mutation) }}" onsubmit="return confirm('Hapus mutasi saldo ini dari daftar? Saldo siswa tidak akan diubah.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-small" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada mutasi saldo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
