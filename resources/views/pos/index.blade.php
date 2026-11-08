
@extends('layouts.app')

@section('title', 'POS')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">OPERASIONAL KANTIN</p>
            <h1>Kasir / Point of Sale</h1>
            <p class="muted">Catat pembelian siswa dengan cepat dan akurat.</p>
        </div>
        <div class="date-chip"><span class="live-dot"></span> Kasir siap</div>
    </div>

    <div class="card pos-card">
        <form method="POST" action="{{ route('pos.checkout') }}">
            @csrf

            <div class="scan-panel">
                <div class="scan-panel-title"><span class="scan-symbol">▣</span><div><strong>Scan kartu siswa</strong><small>Arahkan scanner ke barcode kartu lalu tekan Enter.</small></div><span class="scanner-status"><i></i> Scanner siap</span></div>
                <input id="student_barcode" class="scan-input" type="text" placeholder="Scan barcode kartu siswa..." autocomplete="off" autofocus>
                <small id="student_scan_message" class="scan-message">Scan barcode kartu identitas santri.</small>
            </div>

            <div class="student-picker">
                <div class="section-icon">♙</div>
                <div class="field-grow">
                    <label for="student_id">Siswa yang berbelanja</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" data-barcode="{{ $student->card_token ?: $student->student_number }}" data-balance="{{ $student->balance }}" data-daily-limit="{{ $student->daily_limit }}" data-spent-today="{{ $student->spent_today ?? 0 }}">{{ $student->name }} ({{ $student->classroom }}) - Saldo: Rp {{ number_format($student->balance, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <div id="student-balance-panel" class="student-balance-panel" hidden>
                        <div><small>Saldo saat ini</small><strong id="student-balance">Rp 0</strong></div>
                        <div><small>Limit harian</small><strong id="student-limit">Tidak dibatasi</strong></div>
                        <div><small>Terpakai hari ini</small><strong id="student-spent">Rp 0</strong></div>
                    </div>
                </div>
            </div>

            <div class="scan-panel product-scan-panel">
                <div class="scan-panel-title"><span class="scan-symbol">▤</span><div><strong>Scan barang</strong><small>Scan barcode barang untuk menambahkan ke transaksi.</small></div></div>
                <input id="product_barcode" class="scan-input" type="text" placeholder="Scan barcode produk..." autocomplete="off">
                <small id="product_scan_message" class="scan-message">Setiap scan akan menambah jumlah barang.</small>
            </div>

            <div class="product-header">
                <div>
                    <h2>Pilih produk</h2>
                    <p class="muted">Masukkan jumlah item yang dibeli.</p>
                </div>
                <span class="product-count">{{ $products->count() }} produk aktif</span>
            </div>

            <div class="product-grid">
                @forelse($products as $product)
                    <div class="product-item" data-barcode="{{ $product->barcode }}" data-product-name="{{ $product->name }}">
                        <div class="product-copy">
                            <span class="product-badge">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <small>Stok tersedia: {{ $product->stock }} {{ $product->unit }}</small>
                            </div>
                        </div>
                        <div class="product-action">
                            <span>Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <input class="quantity-input" aria-label="Jumlah {{ $product->name }}" type="number" name="items[{{ $product->id }}]" value="0" min="0" max="{{ $product->stock }}">
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada produk aktif.</div>
                @endforelse
            </div>

            <div class="checkout-bar">
                <div><span class="muted">Pastikan data sudah benar</span><strong>Transaksi akan mengurangi saldo siswa</strong></div>
                <button class="btn btn-success" type="submit"><span>✓</span> Proses Transaksi</button>
            </div>
        </form>
    </div>

    <style>
        .student-balance-panel { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 12px; padding: 12px; border: 1px solid #cfe0d8; border-radius: 10px; background: #f4faf6; }
        .student-balance-panel small, .student-balance-panel strong { display: block; }
        .student-balance-panel small { color: #667085; font-size: 11px; }
        .student-balance-panel strong { margin-top: 4px; color: #183b35; font-size: 15px; }
        @media (max-width: 720px) { .student-balance-panel { grid-template-columns: 1fr; } }
    </style>
    <script>
        (() => {
            const studentScanner = document.querySelector('#student_barcode');
            const productScanner = document.querySelector('#product_barcode');
            const studentSelect = document.querySelector('#student_id');
            const studentMessage = document.querySelector('#student_scan_message');
            const productMessage = document.querySelector('#product_scan_message');
            const products = [...document.querySelectorAll('.product-item[data-barcode]')];
            const studentBalancePanel = document.querySelector('#student-balance-panel');
            const studentBalance = document.querySelector('#student-balance');
            const studentLimit = document.querySelector('#student-limit');
            const studentSpent = document.querySelector('#student-spent');
            let studentTimer;
            let productTimer;

            const cleanCode = (value) => value.trim().replace(/\s/g, '');
            const showMessage = (element, message, isError = false) => {
                element.textContent = message;
                element.classList.toggle('scan-error', isError);
            };

            const processStudentCode = () => {
                const code = cleanCode(studentScanner.value);
                if (code.length < 6) return;
                const option = [...studentSelect.options].find((item) => cleanCode(item.dataset.barcode || '') === code);
                if (!option || !option.value) {
                    showMessage(studentMessage, 'Kartu siswa tidak ditemukan.', true);
                    studentScanner.select();
                    return;
                }
                studentSelect.value = option.value;
                updateStudentSummary();
                showMessage(studentMessage, `Siswa terpilih: ${option.textContent}`);
                productScanner?.focus();
                studentScanner.value = '';
            };

            const rupiah = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;
            const updateStudentSummary = () => {
                const option = studentSelect.options[studentSelect.selectedIndex];
                if (!option?.value) {
                    studentBalancePanel.hidden = true;
                    return;
                }
                studentBalance.textContent = rupiah(option.dataset.balance);
                studentLimit.textContent = Number(option.dataset.dailyLimit || 0) > 0 ? rupiah(option.dataset.dailyLimit) : 'Tidak dibatasi';
                studentSpent.textContent = rupiah(option.dataset.spentToday);
                studentBalancePanel.hidden = false;
            };
            studentSelect?.addEventListener('change', updateStudentSummary);

            studentScanner?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                clearTimeout(studentTimer);
                processStudentCode();
            });
            studentScanner?.addEventListener('input', () => {
                clearTimeout(studentTimer);
                studentTimer = setTimeout(processStudentCode, 300);
            });

            const processProductCode = () => {
                const code = cleanCode(productScanner.value);
                if (code.length < 6) return;
                const product = products.find((item) => cleanCode(item.dataset.barcode || '') === code);
                if (!product) {
                    showMessage(productMessage, 'Barcode produk tidak ditemukan.', true);
                    productScanner.select();
                    return;
                }
                const quantity = product.querySelector('.quantity-input');
                if (Number(quantity.value) >= Number(quantity.max)) {
                    showMessage(productMessage, 'Stok produk sudah mencapai batas.', true);
                    productScanner.select();
                    return;
                }
                quantity.value = Number(quantity.value) + 1;
                showMessage(productMessage, `${product.dataset.productName} ditambahkan. Jumlah: ${quantity.value}`);
                productScanner.value = '';
            };

            productScanner?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                clearTimeout(productTimer);
                processProductCode();
            });
            productScanner?.addEventListener('input', () => {
                clearTimeout(productTimer);
                productTimer = setTimeout(processProductCode, 300);
            });
        })();
    </script>
@endsection
