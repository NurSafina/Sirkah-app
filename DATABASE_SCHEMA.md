# Skema Database Sirkah

Database: MySQL  
Nama database: `sirkah`  
Dibuat oleh migration Laravel dan dapat dilihat atau dikelola melalui phpMyAdmin.

## Tabel Aplikasi

### `users`
Akun pengguna aplikasi.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `name` | Nama pengguna |
| `username` | Username unik untuk login |
| `email` | Email unik |
| `password` | Password yang disimpan dalam bentuk hash |
| `role` | `admin` atau `cashier` |
| `created_at`, `updated_at` | Waktu pencatatan |

### `students`
Data siswa dan saldo uang jajan.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `student_number` | Nomor siswa unik |
| `name` | Nama siswa |
| `classroom` | Kelas |
| `status` | `active` atau `inactive` |
| `balance` | Saldo saat ini |
| `daily_limit` | Batas jajan harian; `0` berarti tanpa batas |
| `notes` | Catatan |

### `products`
Master barang kantin.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `sku` | Kode produk unik |
| `barcode` | Barcode produk unik, opsional |
| `name` | Nama barang |
| `category` | Kategori barang |
| `unit` | Satuan, misalnya `pcs`, `porsi`, atau `gelas` |
| `description` | Deskripsi |
| `price` | Harga jual |
| `cost_price` | Harga modal |
| `stock` | Stok tersedia |
| `minimum_stock` | Batas minimum stok |
| `is_active` | Status barang aktif |

### `transactions`
Nota transaksi POS.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `student_id` | Relasi ke `students` |
| `user_id` | Relasi ke kasir/admin pada `users` |
| `transaction_code` | Nomor transaksi unik |
| `total` | Total belanja |
| `payment_type` | Jenis pembayaran, utama: `saldo` |
| `status` | `completed` atau `cancelled` |
| `notes` | Catatan |
| `created_at`, `updated_at` | Waktu transaksi |

### `transaction_items`
Detail isi nota dengan snapshot nama dan harga saat transaksi.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `transaction_id` | Relasi ke `transactions` |
| `product_id` | Relasi ke `products` |
| `product_name` | Salinan nama saat dibeli |
| `product_price` | Salinan harga saat dibeli |
| `quantity` | Jumlah barang |
| `subtotal` | Harga dikali jumlah |

### `balance_mutations`
Audit seluruh perubahan saldo siswa.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `student_id` | Relasi ke `students` |
| `user_id` | Pengguna pencatat, opsional |
| `type` | `topup`, `purchase`, `refund`, atau `adjustment` |
| `amount` | Nilai perubahan, pembelian bernilai negatif |
| `description` | Keterangan perubahan |
| `reference_type`, `reference_id` | Referensi transaksi terkait |

### `stock_movements`
Audit stok masuk, keluar, dan penyesuaian.

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `product_id` | Relasi ke `products` |
| `user_id` | Pengguna pencatat, opsional |
| `type` | `in`, `out`, atau `adjustment` |
| `quantity` | Jumlah perubahan |
| `reason` | Alasan perubahan |
| `reference_type`, `reference_id` | Referensi transaksi terkait |

## Tabel Bawaan Laravel

- `migrations`: catatan migration yang sudah dijalankan.
- `password_reset_tokens`: token reset password.
- `failed_jobs`: job yang gagal.
- `personal_access_tokens`: token akses Sanctum.

## Relasi Utama

- `students` memiliki banyak `transactions` dan `balance_mutations`.
- `products` memiliki banyak `transaction_items` dan `stock_movements`.
- `transactions` memiliki banyak `transaction_items`.
- `users` mencatat transaksi, mutasi saldo, dan mutasi stok.

## Perintah Menjalankan

```bash
php artisan migrate --seed
php artisan serve
```

Akun awal:

- Admin: `admin` / `password123`
- Kasir: `kasir` / `password123`
