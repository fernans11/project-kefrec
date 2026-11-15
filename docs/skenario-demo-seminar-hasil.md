# Skenario Demo Seminar Hasil Kefrec

Seeder demo menyediakan kondisi aplikasi yang konsisten untuk menunjukkan fitur dari awal sampai akhir tanpa harus membuat seluruh riwayat secara manual.

## Menyiapkan Data

Untuk database kosong (seluruh data lama akan dihapus):

```bash
php artisan migrate:fresh --seed
```

Untuk menambahkan atau menyegarkan data demo tanpa menghapus data yang sudah ada:

```bash
php artisan db:seed --class=DemoScenarioSeeder
```

Seeder dapat dijalankan berulang kali. Data skenario dikenali melalui email domain `@kefrec.test`, invoice berawalan `INV-DEMO`/`PO-DEMO`, serta label `[DEMO]`, lalu diperbarui tanpa membuat duplikat.

> `DemoScenarioSeeder` memerlukan menu dan bahan baku. Pada database baru gunakan `migrate:fresh --seed`. Jika menjalankan seeder demo secara terpisah, pastikan `MenuSeeder` dan `IngredientSeeder` pernah dijalankan.

## Akun Demo

| Aktor | Email | Password | Halaman |
|---|---|---|---|
| Admin | `admin.demo@kefrec.test` | `DemoKefrec123!` | `/admin` |
| Pemilik | `owner.demo@kefrec.test` | `DemoKefrec123!` | `/admin` |
| Kasir | `kasir.demo@kefrec.test` | `DemoKefrec123!` | `/cashier/orders` |
| Dapur | `dapur.demo@kefrec.test` | `DemoKefrec123!` | `/kitchen/orders` |
| Pelanggan | `member01@kefrec.test` s.d. `member50@kefrec.test` | `Member123!` | `/home` |

Gunakan akun hanya untuk demo atau lingkungan lokal. Jangan gunakan password tersebut pada production.

## Cakupan Data

- 50 member terverifikasi dan 3 pelanggan non-member/walk-in.
- 13 menu dalam 3 kategori, lengkap dengan resep bahan baku.
- 112 transaksi demo: 96 selesai, 5 menunggu kasir, 5 diproses dapur, 2 siap diambil, serta contoh `draft`, `pending_payment`, `paid`, dan `cancelled`.
- Metode pembayaran tunai, QRIS, dan transfer, termasuk contoh status Midtrans.
- Transaksi tersebar selama 60 hari agar grafik 7/30 hari dan laporan periodik berisi data.
- Diskon member, pajak, uang dibayar, dan kembalian yang bervariasi.
- 5 supplier, 12 pembelian (`received`, `draft`, `cancelled`), serta detail bahan.
- 4 retur penjualan dan 3 retur pembelian dengan status berbeda.
- 8 komplain (`open`, `progress`, `closed`) beserta respons admin.
- 2 karyawan yang sama dengan akun operasional—Siti Kasir dan Budi Dapur—dengan riwayat absensi 30 hari (`hadir`, `izin`, `sakit`, `alpha`, dan contoh terlambat).
- Cashflow penjualan, pembelian, retur, modal, gaji, operasional, perawatan, dan promosi.
- Pemasukan demo dibuat lebih besar daripada pengeluaran sehingga saldo bersih dashboard tetap positif.
- Audit log aktivitas penting.
- Kondisi stok beragam; Cream Cheese dan Ragi sengaja dibuat menipis untuk memicu peringatan dashboard.

## Urutan Presentasi yang Disarankan

### 1. Dashboard Admin/Pemilik

1. Login sebagai Admin atau Pemilik.
2. Tunjukkan jumlah pesanan, pemasukan, pesanan menunggu kasir, proses dapur, dan stok menipis.
3. Ubah filter grafik pemasukan dari 7 hari ke 30 hari.
4. Tunjukkan komposisi metode pembayaran, produk terlaris, transaksi terbaru, dan cashflow.

Pesan utama: data operasional dari banyak modul terakumulasi otomatis menjadi informasi manajerial.

### 2. Master Menu dan Persediaan

1. Buka kategori dan produk; tampilkan harga, status aktif, kategori, serta recipe.
2. Buka bahan baku; tunjukkan stok normal dan peringatan Cream Cheese/Ragi.
3. Buka supplier untuk menunjukkan sumber pengadaan.

Pesan utama: produk terhubung dengan bahan baku sehingga penyelesaian pesanan memengaruhi persediaan.

### 3. Alur Penjualan End-to-End

Gunakan transaksi live yang sudah disediakan untuk menjelaskan setiap antrean, lalu buat satu transaksi baru agar proses benar-benar terlihat.

1. Login `member01@kefrec.test`, pilih menu, masukkan keranjang, dan checkout tunai.
2. Login Kasir, buka pesanan `pending_cashier`, lalu setujui.
3. Login Dapur, ubah `processing` menjadi `ready`, kemudian `completed`.
4. Kembali ke pelanggan untuk melihat riwayat/status pesanan.
5. Kembali ke admin untuk menunjukkan transaksi, stok bahan berkurang, dan cashflow penjualan bertambah.

Catat stok salah satu bahan sebelum checkout agar perubahan kuantitas dapat dibandingkan setelah transaksi selesai.

### 4. Pembayaran Digital

1. Tampilkan riwayat transaksi QRIS dan transfer yang sudah `settlement`.
2. Tampilkan contoh `pending_payment` yang memiliki token pembayaran demo.
3. Saat internet dan Midtrans Sandbox tersedia, buat checkout digital baru untuk menunjukkan Snap/webhook. Jangan menggunakan transaksi dummy untuk memanggil API Midtrans.

Pesan utama: dataset membuktikan tampilan berbagai status; transaksi Sandbox baru membuktikan integrasi eksternal secara langsung.

### 5. Pembelian dan Stok Masuk

1. Buka pembelian `received` dan detail itemnya.
2. Bandingkan dengan pembelian `draft` dan `cancelled`.
3. Jika melakukan demo interaktif, buat pembelian baru berstatus draft, tambahkan item, lalu ubah menjadi received.
4. Tunjukkan stok bahan bertambah dan cashflow keluar tercatat.

### 6. Retur, Komplain, dan Audit

1. Tampilkan retur penjualan dan pembelian dalam status draft, processed, dan cancelled.
2. Buka komplain open, tindak lanjuti menjadi progress, isi respons, lalu tutup.
3. Tunjukkan audit log untuk memperlihatkan jejak aktivitas pengguna.

### 7. Absensi dan Laporan

1. Login sebagai Kasir/Dapur dan tunjukkan halaman absensi mandiri.
2. Di admin, buka laporan absensi 30 hari untuk menunjukkan hadir, izin, sakit, alpha, dan keterlambatan.
3. Buka laporan periodik, ubah rentang tanggal, lalu cocokkan total penjualan dengan cashflow.

## Validasi Cepat Sebelum Presentasi

Jalankan:

```bash
php artisan test tests/Feature/DemoScenarioSeederTest.php
php artisan test
```

Checklist manual:

- Dashboard dan grafik berisi data.
- Login kelima jenis aktor berhasil.
- Tidak ada invoice ganda.
- Semua transaksi memiliki item.
- Stok produk/bahan tidak negatif.
- Transaksi completed mempunyai cashflow penjualan.
- Pembelian received mempunyai detail item dan cashflow keluar.
- Halaman kasir dan dapur memiliki antrean contoh.

## Reset Setelah Latihan Demo

Cara paling konsisten untuk mengembalikan seluruh data ke titik awal skenario adalah:

```bash
php artisan migrate:fresh --seed
```

Perintah tersebut menghapus seluruh isi database. Gunakan hanya pada database lokal/demo dan lakukan backup jika terdapat data yang perlu disimpan.
