<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Kantin Cashless</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');
        :root { --ink: #17202a; --muted: #718078; --green: #183b35; --green-light: #2e6757; --accent: #f4b85d; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; color: var(--ink); background: #f4f7f3; font-family: 'DM Sans', sans-serif; }
        .register-wrap { display: grid; grid-template-columns: minmax(300px, .85fr) minmax(440px, 1.15fr); min-height: 100vh; }
        .welcome-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: clamp(28px, 5vw, 70px); color: white; background: var(--green); }
        .welcome-panel::before, .welcome-panel::after { position: absolute; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; content: ''; }
        .welcome-panel::before { width: 430px; height: 430px; right: -180px; bottom: -110px; }
        .welcome-panel::after { width: 260px; height: 260px; right: -95px; bottom: -25px; }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 11px; color: white; text-decoration: none; }
        .brand-mark { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 13px; color: var(--green); background: var(--accent); font: 700 23px 'Space Grotesk', sans-serif; }
        .brand strong, .brand small { display: block; }
        .brand strong { font: 700 20px 'Space Grotesk', sans-serif; }
        .brand small { margin-top: 2px; color: #a9cabe; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
        .welcome-copy { position: relative; z-index: 1; max-width: 430px; margin: auto 0; }
        .welcome-copy .eyebrow { color: var(--accent); font-size: 11px; font-weight: 700; letter-spacing: 2px; }
        .welcome-copy h1 { max-width: 390px; margin: 15px 0 18px; font: 700 clamp(34px, 4vw, 58px)/1.05 'Space Grotesk', sans-serif; letter-spacing: -2px; }
        .welcome-copy p { max-width: 370px; margin: 0; color: #bed5c9; font-size: 15px; line-height: 1.7; }
        .register-side { display: grid; place-items: center; padding: 40px clamp(24px, 6vw, 100px); background: #f8faf7; }
        .register-box { width: min(100%, 450px); }
        .register-box h2 { margin: 0 0 8px; font: 700 31px 'Space Grotesk', sans-serif; letter-spacing: -1px; }
        .subtitle { margin: 0 0 26px; color: var(--muted); font-size: 14px; }
        label { display: block; margin-bottom: 8px; color: #33423c; font-size: 13px; font-weight: 700; }
        .field { position: relative; margin-bottom: 16px; }
        input { width: 100%; padding: 12px 14px; border: 1px solid #d9e2dc; border-radius: 9px; color: var(--ink); background: white; font: inherit; outline: none; transition: .2s ease; }
        input:focus { border-color: var(--green-light); box-shadow: 0 0 0 4px rgba(46, 103, 87, .1); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
        button { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; margin-top: 10px; padding: 14px; border: 0; border-radius: 9px; color: white; background: var(--green); box-shadow: 0 8px 18px rgba(24, 59, 53, .18); cursor: pointer; font: 700 14px 'DM Sans', sans-serif; transition: .2s ease; }
        button:hover { background: var(--green-light); transform: translateY(-1px); }
        .error { margin-bottom: 20px; padding: 12px 14px; border: 1px solid #f5c5bb; border-radius: 8px; color: #a33e2f; background: #fff0ed; font-size: 13px; }
        .register-note { margin: 22px 0 0; color: #9aa69f; font-size: 11px; text-align: center; }
        .register-note a { color: var(--green-light); font-weight: 700; text-decoration: none; }
        @media (max-width: 720px) { .register-wrap { display: block; } .welcome-panel { min-height: 255px; padding: 26px 22px; } .welcome-copy { margin: 38px 0 0; } .welcome-copy h1 { margin: 10px 0; font-size: 34px; } .welcome-copy p { font-size: 13px; line-height: 1.5; } .register-side { min-height: calc(100vh - 255px); padding: 38px 22px; } }
        @media (max-width: 430px) { .form-grid { display: block; } }
    </style>
</head>
<body>
    <div class="register-wrap">
        <section class="welcome-panel">
            <a class="brand" href="{{ route('login') }}">
                <span class="brand-mark">K</span>
                <span><strong>Kantin</strong><small>Cashless system</small></span>
            </a>
            <div class="welcome-copy">
                <span class="eyebrow">AKSES PENGELOLA</span>
                <h1>Bangun kerja kasir yang lebih rapi.</h1>
                <p>Buat akun untuk mulai mengelola transaksi dan operasional kantin sekolah.</p>
            </div>
        </section>

        <main class="register-side">
            <div class="register-box">
                <h2>Buat akun kasir</h2>
                <p class="subtitle">Lengkapi data berikut untuk mendaftarkan akun baru.</p>

                @if ($errors->any())
                    <div class="error">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}">
                    @csrf
                    <label for="name">Nama lengkap</label>
                    <div class="field">
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" autocomplete="name" required>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label for="username">Username</label>
                            <div class="field">
                                <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="budi_kasir" pattern="[A-Za-z0-9_-]+" title="Gunakan hanya huruf, angka, tanda hubung (-), dan underscore (_)." autocomplete="username" required>
                            </div>
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <div class="field">
                                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="budi@email.com" autocomplete="email" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label for="password">Password</label>
                            <div class="field">
                                <input id="password" name="password" type="password" placeholder="Min. 8 karakter" autocomplete="new-password" required>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation">Ulangi password</label>
                            <div class="field">
                                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ketik ulang" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit">Buat akun <span>→</span></button>
                </form>
                <p class="register-note">Sudah punya akun? <a href="{{ route('login') }}">Kembali ke login</a></p>
            </div>
        </main>
    </div>
</body>
</html>
