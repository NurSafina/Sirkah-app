<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Baru | Kantin Cashless</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');
        * { box-sizing: border-box; }
        body { min-height: 100vh; display: grid; place-items: center; margin: 0; color: #17202a; background: #f4f7f3; font-family: 'DM Sans', sans-serif; }
        .box { width: min(92%, 440px); padding: 35px; border: 1px solid #e0e9e2; border-radius: 16px; background: white; box-shadow: 0 18px 45px rgba(24,59,53,.08); }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 34px; color: #183b35; text-decoration: none; }
        .mark { display: grid; place-items: center; width: 39px; height: 39px; border-radius: 11px; color: #183b35; background: #f4b85d; font: 700 21px 'Space Grotesk'; }
        .brand strong { font: 700 19px 'Space Grotesk'; }
        .brand small { display: block; color: #84938b; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
        h1 { margin: 0 0 9px; font: 700 28px 'Space Grotesk'; letter-spacing: -.8px; }
        .intro { margin: 0 0 26px; color: #718078; font-size: 13px; line-height: 1.6; }
        label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; }
        input { width: 100%; margin-bottom: 16px; padding: 13px; border: 1px solid #d9e2dc; border-radius: 9px; font: inherit; outline: none; }
        input:focus { border-color: #2e6757; box-shadow: 0 0 0 4px rgba(46,103,87,.1); }
        button { width: 100%; padding: 13px; border: 0; border-radius: 9px; color: white; background: #183b35; cursor: pointer; font: 700 14px 'DM Sans'; }
        .error { margin-bottom: 18px; padding: 11px 13px; border-radius: 8px; color: #a33e2f; background: #fff0ed; font-size: 12px; }
        .back { display: block; margin-top: 22px; color: #2e6757; font-size: 12px; font-weight: 700; text-align: center; text-decoration: none; }
    </style>
</head>
<body>
    <main class="box">
        <a class="brand" href="{{ route('login') }}"><span class="mark">K</span><span><strong>Kantin</strong><small>Cashless system</small></span></a>
        <h1>Buat password baru</h1>
        <p class="intro">Gunakan password baru yang kuat, minimal 8 karakter.</p>
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">Email akun</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="nama@email.com" autocomplete="email" required>
            <label for="password">Password baru</label>
            <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
            <label for="password_confirmation">Ulangi password baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ketik ulang password" autocomplete="new-password" required>
            <button type="submit">Simpan password baru</button>
        </form>
        <a class="back" href="{{ route('login') }}">← Kembali ke login</a>
    </main>
</body>
</html>
