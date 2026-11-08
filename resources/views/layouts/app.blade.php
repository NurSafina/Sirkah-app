<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1f6f4a">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">K</span>
                <span><strong>Kantin</strong><small>Cashless system</small></span>
            </a>
            <nav>
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>Dashboard</a>
                <a class="{{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 8h14l-1 12H6L5 8Z"/><path d="M8 8a4 4 0 0 1 8 0M12 12v5M9.5 14.5h5"/></svg></span>Kasir / POS</a>
                <a class="{{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h13l-3-3M20 17H7l3 3M17 7l3 3-3 3M7 17l-3-3 3-3"/></svg></span>Transaksi</a>
                @if (auth()->user()->isAdmin() || auth()->user()->isCashier())
                    <a class="{{ request()->routeIs('stock.*') ? 'active' : '' }}" href="{{ route('stock.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4.5 7.5 7.5 4 7.5-4M12 12v9M8 5.2l8 4.4"/></svg></span>Stok Harian</a>
                @endif
                @if (auth()->user()->isAdmin() || auth()->user()->isCashier())
                    <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.5 7.5 4 7.5-4M12 12v9"/></svg></span>Produk</a>
                @endif
                @if (auth()->user()->isAdmin())
                    <a class="{{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Akun Kasir</a>
                    <a class="{{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0M18 5.5a3 3 0 0 1 0 5.5"/></svg></span>Siswa</a>
                    <a class="{{ request()->routeIs('balance-mutations.*') ? 'active' : '' }}" href="{{ route('balance-mutations.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 18h16"/><path d="M7 14V8"/><path d="M12 14V6"/><path d="M17 14v-4"/><path d="M5 6l7-3 7 3"/></svg></span>Mutasi Saldo</a>
                    <a class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V5M4 19h16"/><path d="m7 15 3-4 3 2 5-6"/><circle cx="7" cy="15" r="1"/><circle cx="10" cy="11" r="1"/><circle cx="13" cy="13" r="1"/><circle cx="18" cy="7" r="1"/></svg></span>Laporan</a>
                    <a class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">Audit Log</a>
                    <a class="{{ request()->routeIs('whatsapp-notifications.*') ? 'active' : '' }}" href="{{ route('whatsapp-notifications.index') }}">Notifikasi WA</a>
                @endif
            </nav>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"><span class="sidebar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14 4H5v16h9M10 12h10M17 9l3 3-3 3"/></svg></span>Keluar</button>
            </form>
        </aside>

        <main class="content">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});
    </script>
</body>
</html>
