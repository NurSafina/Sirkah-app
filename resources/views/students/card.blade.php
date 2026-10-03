<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Pelajar - {{ $student->name }}</title>
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: Arial, sans-serif; margin: 24px; color: #0b1f3a; }
        .print-sheet { display: flex; flex-direction: column; gap: 10mm; align-items: center; width: 100%; }
        .card { position: relative; width: 85.60mm; height: 53.98mm; border: 1px solid #0b1f3a; border-radius: 1mm; overflow: hidden; background: #fff; color: #0b1f3a; }
        .card-header { position: relative; height: 16mm; padding: 3mm 5mm; display: flex; align-items: center; justify-content: center; background: linear-gradient(166deg, #142f52 0%, #0b1f3a 58%, #07162a 59%, #07162a 100%); color: #fff; }
        .card-header::after { content: ''; position: absolute; left: -5%; right: -5%; bottom: -5mm; height: 9mm; background: #fff; border-radius: 50% 50% 0 0 / 80% 80% 0 0; }
        .card-header h2 { position: relative; z-index: 2; margin: 0; color: #fff; font-size: 13px; letter-spacing: .2px; text-align: center; line-height: 1.05; }
        .card-header h2 span { display: block; color: #fff; font-size: 9px; letter-spacing: .5px; margin-top: 1px; }
        .pondok-logo { position: absolute; z-index: 3; left: 3mm; top: 3mm; width: 22mm; height: 8mm; object-fit: contain; background: transparent; }
        .pondok-logo-placeholder { position: absolute; z-index: 3; left: 3mm; top: 3mm; width: 22mm; height: 8mm; display: grid; place-items: center; border: 1px solid #fff; border-radius: 1mm; font-size: 5px; line-height: 1; text-align: center; color: #0b1f3a; background: #fff; }
        .card-body { display: flex; gap: 3mm; height: 29mm; padding: 1mm 4mm 0; align-items: center; }
        .photo { width: 21mm; height: 25mm; object-fit: cover; border-radius: 1mm; border: 1px solid #b9c7d8; } .photo-placeholder { display: grid; place-items: center; background: #e6edf5; color: #29476a; font-size: 8px; }
        .identity { flex: 1; display: block; text-align: left; min-width: 0; padding-right: 26mm; } .identity p { color: #0b1f3a; font-size: 7px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; } .identity p strong { font-size: 9px; } p { margin: 1.5px 0; } .field-label { display: inline-block; width: 19mm; font-weight: 700; }
        .front-barcode-wrap { position: absolute; z-index: 4; right: 3.5mm; bottom: 7mm; width: 19mm; text-align: center; }
        .front-barcode-wrap #front-qrcode { display: flex; align-items: center; justify-content: center; width: 19mm; height: 19mm; margin: 0 auto .5mm; padding: 1mm; background: #fff; }
        .front-barcode-wrap #front-qrcode img, .front-barcode-wrap #front-qrcode canvas { display: block; width: 17mm !important; height: 17mm !important; }
        .front-barcode-wrap strong { display: block; color: #0b1f3a; font-size: 4.5px; letter-spacing: .2px; }
        .card-footer { height: 7mm; padding: 1.5mm 4mm; display: flex; align-items: center; justify-content: center; text-align: center; background: #0b1f3a; color: #fff; font-size: 6.5px; line-height: 1.1; }
        .back-card { display: flex; flex-direction: column; justify-content: space-between; padding: 7mm 8mm 5mm; text-align: center; background: linear-gradient(145deg, #fff 0%, #f4f7fb 100%); }
        .back-card h3 { margin: 0; color: #0b1f3a; font-size: 11px; letter-spacing: .4px; }
        .back-card p { font-size: 7px; color: #34516f; }
        .barcode-wrap { margin: 0 auto; text-align: center; width: 58mm; }
        .barcode-wrap svg { display: block; width: 58mm; height: 20mm; margin: 2mm auto 1mm; background: #fff; }
        .barcode-wrap strong { display: block; font-size: 8px; letter-spacing: .5px; color: #0b1f3a; }
        .back-note { border-top: 1px solid #b9c7d8; padding-top: 3mm; }
        .actions { margin-top: 18px; } button { padding: 8px 14px; }
        @media print {
            html, body { width: 100%; min-height: 100%; margin: 0; background: #fff !important; }
            body, .card, .card-header, .card-header::after, .card-footer, .back-card, .photo-placeholder, .barcode-wrap svg, .pondok-logo-placeholder {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .actions { display: none; }
            .print-sheet { gap: 5mm; align-items: center; justify-content: flex-start; width: 100%; padding-top: 10mm; }
            .card { flex: 0 0 auto; }
            @page { size: A4 portrait; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-sheet">
        <div class="card front-card">
            <div class="card-header">
                <h2>KARTU PELAJAR<span>{{ config('sirkah.school_name') }}</span></h2>
                @if (file_exists(public_path('images/logo-pondok-transparent.png')))
                    <img class="pondok-logo" src="{{ asset('images/logo-pondok-transparent.png') }}" alt="Logo Humaira Quran Center">
                @else
                    <div class="pondok-logo-placeholder">LOGO<br>PONDOK</div>
                @endif
            </div>
            <div class="card-body">
                @if ($student->photo_path)
                    <img class="photo" src="{{ asset('storage/' . $student->photo_path) }}" alt="Foto {{ $student->name }}">
                @else
                    <div class="photo photo-placeholder">Tanpa foto</div>
                @endif
                <div class="identity">
                    <p><strong>{{ $student->name }}</strong></p>
                    <p><span class="field-label">No. Identitas</span>: {{ $student->student_number }}</p>
                    <p><span class="field-label">Kode Kartu</span>: {{ $student->card_code }}</p>
                    <p><span class="field-label">Kelas</span>: {{ $student->classroom }}</p>
                </div>
            </div>
            <div class="front-barcode-wrap"><div id="front-qrcode" aria-label="QR Code {{ $student->card_code }}"></div><strong>{{ $student->card_code }}</strong></div>
            <div class="card-footer">Alamat: {{ config('sirkah.school_address') }}</div>
        </div>

        <div class="card back-card">
            <div><h3>KARTU PELAJAR SIRKAH</h3><p>Gunakan barcode ini untuk transaksi kantin.</p></div>
            <div class="barcode-wrap"><svg id="barcode"></svg><strong>{{ $student->card_code }}</strong></div>
            <div class="back-note"><p>Kartu ini adalah identitas milik {{ $student->name }}. Jika ditemukan, harap dikembalikan kepada pihak pondok.</p></div>
        </div>
    </div>
    <div class="actions"><button onclick="window.print()">Cetak Kartu Depan & Belakang</button></div>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        const cardToken = @json($student->card_token);
        JsBarcode('#barcode', cardToken, {format: 'CODE128', displayValue: false, height: 72, margin: 0});
        new QRCode(document.getElementById('front-qrcode'), {
            text: cardToken,
            width: 180,
            height: 180,
            colorDark: '#0b1f3a',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>
