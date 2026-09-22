@extends('layouts.app')

@section('title', 'Siswa')

@section('content')
    <div class="students-layout">
        <div class="card">
            <h3>{{ $editingStudent ? 'Edit Siswa' : 'Tambah Siswa' }}</h3>
            <form method="POST" enctype="multipart/form-data" action="{{ $editingStudent ? route('students.update', $editingStudent) : route('students.store') }}">
                @csrf
                @if ($editingStudent)
                    @method('PUT')
                @endif
                <label>Nomor Siswa</label>
                <input type="text" name="student_number" value="{{ old('student_number', $editingStudent?->student_number) }}" required>

                <label>Nama</label>
                <input type="text" name="name" value="{{ old('name', $editingStudent?->name) }}" required>

                <label>Nama Orang Tua/Wali</label>
                <input type="text" name="parent_name" value="{{ old('parent_name', $editingStudent?->parent_name) }}">

                <label>Nomor WhatsApp Orang Tua/Wali</label>
                <input type="text" name="parent_phone" value="{{ old('parent_phone', $editingStudent?->parent_phone) }}" placeholder="628xxxxxxxxxx">

                <label>Foto Santri</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                @if ($editingStudent?->photo_path)
                    <small class="muted">Foto saat ini tersimpan. Pilih file baru untuk menggantinya.</small>
                @endif

                <label>Kelas</label>
                <input type="text" name="classroom" value="{{ old('classroom', $editingStudent?->classroom) }}" required>

                <label>Status</label>
                <select name="status">
                    <option value="active" @selected(old('status', $editingStudent?->status ?? 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $editingStudent?->status) === 'inactive')>Nonaktif</option>
                </select>

                <label>Limit Jajan Harian (0 = tanpa limit)</label>
                <input type="number" name="daily_limit" value="{{ old('daily_limit', $editingStudent?->daily_limit ?? 0) }}" min="0" step="1000" required>

                <label>Catatan</label>
                <textarea name="notes" rows="3">{{ old('notes', $editingStudent?->notes) }}</textarea>

                <button class="btn btn-primary" type="submit">{{ $editingStudent ? 'Perbarui Siswa' : 'Simpan Siswa' }}</button>
                @if ($editingStudent)
                    <a class="btn btn-secondary" href="{{ route('students.index') }}">Batal</a>
                @endif
            </form>
        </div>

        <div class="card">
            <h3>Daftar Siswa</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Saldo</th>
                        <th>Kartu</th>
                        <th>Top Up</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                @if ($student->photo_path)<img src="{{ asset('storage/' . $student->photo_path) }}" alt="Foto {{ $student->name }}" style="width:42px;height:42px;object-fit:cover;border-radius:50%;vertical-align:middle;margin-right:8px;">@endif
                                {{ $student->name }}<br><small class="muted">{{ $student->student_number }}</small>
                            </td>
                            <td>{{ $student->classroom }}</td>
                            <td>{{ $student->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
                            <td>Rp {{ number_format($student->balance, 0, ',', '.') }}</td>
                            <td>
                                <small>{{ $student->card_status === 'active' ? 'Aktif' : 'Nonaktif' }}</small><br>
                                <small class="muted">{{ $student->card_code }}</small>
                                <form method="POST" action="{{ route('students.toggle-card', $student) }}" class="inline">
                                    @csrf
                                    <button class="btn btn-secondary btn-small" type="submit">{{ $student->card_status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                                <form method="POST" action="{{ route('students.regenerate-card', $student) }}" class="inline" onsubmit="return confirm('Terbitkan barcode baru? Barcode lama tidak dapat digunakan.')">
                                    @csrf
                                    <button class="btn btn-secondary btn-small" type="submit">Terbitkan Ulang</button>
                                </form>
                                <a class="btn btn-secondary btn-small" target="_blank" href="{{ route('students.card', $student) }}">Cetak Kartu</a>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('students.topup', $student->id) }}" class="inline topup-form">
                                    @csrf
                                    <div class="topup-amount">
                                        <input type="number" name="amount" min="1000" step="1000" placeholder="Jumlah"
                                               required>
                                        <div class="topup-presets" aria-label="Pilihan nominal top up">
                                            <button class="btn btn-amount" type="button" data-topup-amount="10000">Rp 10.000</button>
                                            <button class="btn btn-amount" type="button" data-topup-amount="20000">Rp 20.000</button>
                                            <button class="btn btn-amount" type="button" data-topup-amount="50000">Rp 50.000</button>
                                            <button class="btn btn-amount" type="button" data-topup-amount="100000">Rp 100.000</button>
                                        </div>
                                        <input type="text" name="reference_number" placeholder="No. referensi (opsional)">
                                        <input type="text" name="description" placeholder="Catatan top-up (opsional)">
                                    </div>
                                    <button class="btn btn-success" type="submit">Top Up</button>
                                </form>
                            </td>
                            <td>
                                <a class="btn btn-secondary btn-small" href="{{ route('students.index', ['edit' => $student->id]) }}">Edit</a>
                                    <form method="POST" action="{{ route('students.destroy', $student) }}" class="inline" onsubmit="return confirm('Hapus siswa ini? Histori transaksi dan mutasi tetap disimpan tanpa data siswa.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-small" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-topup-amount]').forEach((button) => {
            button.addEventListener('click', () => {
                const amountInput = button.closest('.topup-form').querySelector('[name="amount"]');
                amountInput.value = button.dataset.topupAmount;
                amountInput.focus();
            });
        });
    </script>
@endpush
