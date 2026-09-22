# Sirkah App

Sistem operasional kantin sekolah cashless berbasis Laravel. MVP mencakup login admin/kasir, master siswa dan produk, top-up saldo, POS dengan pemotongan saldo otomatis, mutasi stok, histori transaksi, dan laporan pendapatan.

## Menjalankan dengan MySQL/phpMyAdmin

1. Buat database bernama `sirkah` melalui phpMyAdmin.
2. Pastikan `.env` berisi koneksi MySQL yang benar (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`).
3. Jalankan `php artisan optimize:clear` agar konfigurasi lama tidak dipakai.
4. Jalankan `php artisan migrate --seed`, lalu `php artisan serve`.

Login awal: `admin` / `password123` atau `kasir` / `password123`.

phpMyAdmin digunakan untuk mengelola database MySQL; tabel dibentuk otomatis oleh migration Laravel.

## Tabel Database

| Tabel | Fungsi |
| --- | --- |
| `users` | Akun admin dan kasir, role, username, password hash |
| `students` | Data siswa, status, saldo, dan limit jajan harian |
| `products` | Master barang, barcode, kategori, harga, satuan, dan stok |
| `transactions` | Nota transaksi POS, kasir, total, status, dan timestamp |
| `transaction_items` | Detail barang transaksi dengan snapshot nama dan harga |
| `balance_mutations` | Audit top-up, pembelian, refund, dan penyesuaian saldo |
| `stock_movements` | Audit stok masuk dan stok keluar |
| `password_reset_tokens` | Token reset password Laravel |
| `failed_jobs` | Daftar job gagal Laravel |
| `personal_access_tokens` | Token akses Sanctum |

## Aturan MVP

- Transaksi ditolak jika saldo tidak cukup, stok berubah/tidak cukup, atau melewati limit jajan harian.
- Saldo dan stok dikunci selama checkout agar aman saat ada kasir bersamaan.
- Transaksi berhasil tidak dihapus; pembatalan/refund perlu dibuat sebagai alur lanjutan.
- Harga dan nama produk disimpan sebagai snapshot pada `transaction_items`.

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
