<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Kantin Cashless</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');
        :root { --ink: #17202a; --muted: #718078; --green: #183b35; --green-light: #2e6757; --accent: #f4b85d; --coral: #ef755f; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; color: var(--ink); background: #f4f7f3; font-family: 'DM Sans', sans-serif; }
        .login-wrap { display: grid; grid-template-columns: minmax(300px, .9fr) minmax(420px, 1.1fr); min-height: 100vh; }
        .welcome-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: clamp(28px, 5vw, 70px); color: white; background: var(--green); }
        .welcome-panel::before, .welcome-panel::after { position: absolute; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; content: ''; }
        .welcome-panel::before { width: 430px; height: 430px; right: -180px; bottom: -110px; }
        .welcome-panel::after { width: 260px; height: 260px; right: -95px; bottom: -25px; }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 11px; color: white; text-decoration: none; }
        .brand-mark { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 13px; color: var(--green); background: var(--accent); font: 700 23px 'Space Grotesk', sans-serif; }
        .brand strong, .brand small { display: block; }
        .brand strong { font: 700 20px 'Space Grotesk', sans-serif; }
        .brand small { margin-top: 2px; color: #a9cabe; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
        .welcome-copy { position: relative; z-index: 1; max-width: 440px; margin: auto 0; }
        .welcome-copy .eyebrow { color: var(--accent); font-size: 11px; font-weight: 700; letter-spacing: 2px; }
        .welcome-copy h1 { max-width: 390px; margin: 15px 0 18px; font: 700 clamp(34px, 4vw, 58px)/1.05 'Space Grotesk', sans-serif; letter-spacing: -2px; }
        .welcome-copy p { max-width: 370px; margin: 0; color: #bed5c9; font-size: 15px; line-height: 1.7; }
        .feature-row { position: relative; z-index: 1; display: flex; gap: 24px; color: #c6ded2; font-size: 12px; }
        .feature-row span::before { margin-right: 7px; color: var(--accent); content: '✓'; font-weight: 700; }
        .login-side { display: grid; place-items: center; padding: 40px clamp(24px, 6vw, 100px); background: #f8faf7; }
        .login-box { width: min(100%, 420px); }
        .login-box h2 { margin: 0 0 8px; font: 700 31px 'Space Grotesk', sans-serif; letter-spacing: -1px; }
        .subtitle { margin: 0 0 30px; color: var(--muted); font-size: 14px; }
        label { display: block; margin-bottom: 8px; color: #33423c; font-size: 13px; font-weight: 700; }
        .field { position: relative; margin-bottom: 18px; }
        .field-icon { position: absolute; top: 12px; left: 14px; color: #91a199; font-size: 16px; }
        input { width: 100%; padding: 13px 14px 13px 42px; border: 1px solid #d9e2dc; border-radius: 9px; color: var(--ink); background: white; font: inherit; outline: none; transition: .2s ease; }
        .password-field input { padding-right: 46px; }
        .password-toggle { position: absolute; top: 50%; right: 9px; display: grid; place-items: center; width: 32px; height: 32px; padding: 0; margin: 0; border: 0; border-radius: 7px; color: #91a199; background: transparent; box-shadow: none; transform: translateY(-50%); }
        .password-toggle:hover { color: var(--green-light); background: #eef5f0; box-shadow: none; transform: translateY(-50%); }
        .password-toggle:focus-visible { outline: 2px solid var(--green-light); outline-offset: 1px; }
        .password-toggle svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .password-toggle .eye-off { display: none; }
        .password-toggle.is-visible .eye { display: none; }
        .password-toggle.is-visible .eye-off { display: block; }
        input:focus { border-color: var(--green-light); box-shadow: 0 0 0 4px rgba(46, 103, 87, .1); }
        button { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; margin-top: 25px; padding: 14px; border: 0; border-radius: 9px; color: white; background: var(--green); box-shadow: 0 8px 18px rgba(24, 59, 53, .18); cursor: pointer; font: 700 14px 'DM Sans', sans-serif; transition: .2s ease; }
        button:hover { background: var(--green-light); transform: translateY(-1px); }
        .arrow { font-size: 18px; line-height: 0; }
        .error, .success { margin-bottom: 20px; padding: 12px 14px; border-radius: 8px; font-size: 13px; }
        .error { border: 1px solid #f5c5bb; color: #a33e2f; background: #fff0ed; }
        .success { border: 1px solid #b9dfc1; color: #236d43; background: #e8f7eb; }
        .forgot-link { display: block; margin-top: -4px; color: var(--green-light); font-size: 12px; font-weight: 700; text-align: right; text-decoration: none; }
        .login-note { margin: 25px 0 0; color: #9aa69f; font-size: 11px; text-align: center; }
        @media (max-width: 720px) { .login-wrap { display: block; } .welcome-panel { min-height: 290px; padding: 26px 22px; } .welcome-copy { margin: 42px 0 0; } .welcome-copy h1 { margin: 10px 0; font-size: 34px; } .welcome-copy p { font-size: 13px; line-height: 1.5; } .feature-row { display: none; } .login-side { min-height: calc(100vh - 290px); padding: 38px 22px; } }
    </style>
</head>
<body>
    <div class="login-wrap">
        <section class="welcome-panel">
            <a class="brand" href="#">
                <span class="brand-mark">K</span>
                <span><strong>Kantin</strong><small>Cashless system</small></span>
            </a>
            <div class="welcome-copy">
                <span class="eyebrow">RUANG KERJA KASIR</span>
                <h1>Transaksi lebih ringan, setiap hari.</h1>
                <p>Kelola pembelian siswa, saldo, dan stok kantin dalam satu tempat yang sederhana.</p>
            </div>
            <div class="feature-row"><span>Data terorganisir</span><span>Proses lebih cepat</span></div>
        </section>

        <main class="login-side">
            <div class="login-box">
                <h2>Selamat datang</h2>
                <p class="subtitle">Masuk untuk melanjutkan ke dashboard kantin.</p>

                @if ($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif
                @if (session('status'))
                    <div class="success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <label for="username">Username</label>
                    <div class="field">
                        <span class="field-icon">♙</span>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" required>
                    </div>

                    <label for="password">Password</label>
                    <div class="field password-field">
                        <span class="field-icon">●</span>
                        <input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17.6 17.6 0 0 1-3.1 3.7M6.4 6.6C3.9 8.1 2.5 12 2.5 12s3.5 6 9.5 6c1.2 0 2.3-.2 3.2-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                    <a class="forgot-link" href="{{ route('password.request') }}">Lupa password?</a>

                    <button type="submit">Masuk ke dashboard <span class="arrow">→</span></button>
                </form>
            </div>
        </main>
    </div>
    <script>
        const passwordInput = document.querySelector('#password');
        const passwordToggle = document.querySelector('.password-toggle');

        passwordToggle?.addEventListener('click', () => {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            passwordToggle.classList.toggle('is-visible', !isVisible);
            passwordToggle.setAttribute('aria-pressed', String(!isVisible));
            passwordToggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
        });
    </script>
</body>
</html>
