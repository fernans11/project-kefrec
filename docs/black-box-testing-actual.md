# Laporan Black Box Testing — Project KEFREC

## 1. Ringkasan pengujian

| Item | Hasil aktual |
|---|---|
| Tanggal eksekusi | 2 Agustus 2026 (Asia/Jakarta) |
| Perintah | `php artisan test` |
| Lingkungan | Laravel 12, PHP 8.2+, SQLite `:memory:`, session/cache in-memory |
| Cakupan | HTTP endpoint, autentikasi, Livewire, otorisasi per peran, transaksi, stok, pembelian, laporan, dan data demo |
| Total hasil | 41 passed, 7 skipped, 0 failed |
| Assertion | 118 |
| Durasi | 104,69 detik |
| Kesimpulan | Semua skenario yang aktif dan dieksekusi sesuai expected result |

> **Catatan metode:** hasil di bawah berasal dari eksekusi nyata suite pada repository ini, bukan data perkiraan. Pengujian menggunakan database SQLite sementara sehingga tidak mengubah database operasional. Pengujian mencakup lapisan aplikasi melalui request HTTP/Livewire dan pemeriksaan keluaran/persistensi. Belum termasuk pengujian UI melalui browser sungguhan, kompatibilitas perangkat, performa, dan integrasi pembayaran Midtrans secara live.

## 2. Tabel hasil black box aktual

### A. Halaman publik, autentikasi, dan registrasi

| ID | Skenario | Data uji / aksi | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| BB-001 | Membuka landing page | `GET /` sebagai guest | Halaman dapat dibuka | HTTP 200 diterima | Lulus |
| BB-002 | Membuka halaman login | `GET /login` | Form login tampil | HTTP 200 diterima | Lulus |
| BB-003 | Login valid | User factory; email user terdaftar; password `password` | User terautentikasi dan diarahkan ke dashboard | Session terautentikasi; redirect ke `/dashboard` | Lulus |
| BB-004 | Login dengan password salah | Email terdaftar; password `wrong-password` | Login ditolak | User tetap berstatus guest | Lulus |
| BB-005 | Membuka registrasi | `GET /register` saat fitur registrasi aktif | Form registrasi tampil | HTTP 200 diterima | Lulus |
| BB-006 | Registrasi user baru | Nama `Test User`; email `test@example.com`; password dan konfirmasi `password`; persetujuan terms sesuai konfigurasi | Akun dibuat, user login, lalu diarahkan ke dashboard | User terautentikasi; redirect ke `/dashboard` | Lulus |

### B. Profil, password, sesi, dan keamanan akun

| ID | Skenario | Data uji / aksi | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| BB-007 | Menampilkan data profil saat ini | User terautentikasi membuka form profil | Nama dan email form sama dengan data user | State Livewire memuat nama dan email yang sama | Lulus |
| BB-008 | Memperbarui profil | Nama `Test Name`; email `test@example.com` | Data user berubah | Nama dan email baru tersimpan di database | Lulus |
| BB-009 | Mengubah password valid | Current `password`; baru `new-password`; konfirmasi `new-password` | Password berubah | Hash password baru cocok dengan `new-password` | Lulus |
| BB-010 | Mengubah password dengan current password salah | Current `wrong-password`; baru dan konfirmasi `new-password` | Validasi gagal dan password lama tetap berlaku | Error `current_password`; hash tetap cocok dengan `password` | Lulus |
| BB-011 | Konfirmasi password baru tidak sama | Current `password`; baru `new-password`; konfirmasi `wrong-password` | Validasi gagal dan password tidak berubah | Error pada field `password`; password lama tetap berlaku | Lulus |
| BB-012 | Membuka konfirmasi password | User login; `GET /user/confirm-password` | Halaman tampil | HTTP 200 diterima | Lulus |
| BB-013 | Konfirmasi password valid | Password `password` | Konfirmasi diterima | Redirect terjadi tanpa session error | Lulus |
| BB-014 | Konfirmasi password salah | Password `wrong-password` | Konfirmasi ditolak | Session memiliki validation error | Lulus |
| BB-015 | Membuka lupa password | `GET /forgot-password` | Halaman tampil | HTTP 200 diterima | Lulus |
| BB-016 | Meminta tautan reset password | Email user terdaftar | Notifikasi reset dikirim | Notifikasi `ResetPassword` tercatat terkirim | Lulus |
| BB-017 | Membuka form reset dari token | Token yang dikirim untuk user | Form reset tampil | HTTP 200 diterima | Lulus |
| BB-018 | Reset password dengan token valid | Token valid; email user; password dan konfirmasi `password` | Reset diterima | Respons tidak memiliki session error | Lulus |
| BB-019 | Logout sesi browser lain | User login; password `password` | Sesi lain dikeluarkan | Aksi Livewire selesai sukses | Lulus |
| BB-020 | Menghapus akun dengan password benar | User login; password `password` | Akun terhapus | Record user tidak ditemukan setelah aksi | Lulus |
| BB-021 | Menghapus akun dengan password salah | Password `wrong-password` | Penghapusan ditolak | Error field `password`; record user tetap ada | Lulus |
| BB-022 | Mengaktifkan 2FA | User login dan password sudah dikonfirmasi | Secret dan recovery code dibuat | Secret tersimpan; 8 recovery code tersedia | Lulus |
| BB-023 | Regenerasi recovery code 2FA | 2FA aktif; regenerasi dua kali | Kode lama diganti dengan kode baru | Tetap 8 kode; 8 kode lama berbeda dari set terbaru | Lulus |
| BB-024 | Menonaktifkan 2FA | User dengan 2FA aktif | Secret 2FA dihapus | `two_factor_secret` menjadi null | Lulus |

### C. Hak akses, pelanggan, kasir, dan dapur

| ID | Skenario | Data uji / aksi | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| BB-025 | Workspace akun demo sesuai peran | Admin/owner membuka `/admin`; kasir `/cashier/orders`; dapur `/kitchen/orders` dan `/kitchen/ingredients` | Masing-masing peran dapat membuka workspace miliknya | Semua endpoint mengembalikan HTTP 200; 50 akun member demo ditemukan | Lulus |
| BB-026 | Admin membuka data bahan baku | Login guard admin sebagai `Admin Tester`; `GET /admin/ingredients` | Halaman dapat dibuka dan memuat judul bahan baku | HTTP 200 dan teks `Bahan Baku` tampil | Lulus |
| BB-027 | Member dilarang membuka halaman kasir | `member02@kefrec.test`; `GET /cashier/orders` | Akses ditolak | HTTP 403 diterima | Lulus |
| BB-028 | Kasir dilarang membuka halaman dapur | `kasir.demo@kefrec.test`; `GET /kitchen/orders` | Akses ditolak | HTTP 403 diterima | Lulus |
| BB-029 | Dapur dilarang membuka halaman kasir | `dapur.demo@kefrec.test`; `GET /cashier/orders` | Akses ditolak | HTTP 403 diterima | Lulus |
| BB-030 | Dapur mencoba menyelesaikan order yang masih processing | Order status `processing`; POST aksi completed | Aksi ditolak dan status tidak berubah | Session berisi error; status tetap `processing` | Lulus |
| BB-031 | Member melihat order sendiri | `member03@kefrec.test` membuka detail order miliknya | Detail dapat dibuka | HTTP 200 diterima | Lulus |
| BB-032 | Member melihat order member lain | `member04@kefrec.test` membuka order member03 | Akses ditolak | HTTP 403 diterima | Lulus |
| BB-033 | Member mengirim komplain | Judul `Pengujian komplain saat seminar`; pesan pengujian; member03 | Komplain tersimpan untuk customer tersebut | Session sukses; record judul dan `customer_id` ditemukan | Lulus |
| BB-034 | Pengelompokan role internal | Baca konfigurasi role staf | Hanya admin, owner, kasir, dapur masuk akun internal | Nilai aktual persis `[admin, owner, kasir, dapur]`; `user` tidak termasuk | Lulus |
| BB-035 | Label role bisnis | Role admin, owner, kasir, dapur, user | Label mudah dipahami | Aktual: Admin, Owner, Karyawan, Karyawan, Pelanggan | Lulus |

### D. Transaksi penjualan dan inventori

| ID | Skenario | Data uji / aksi | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| BB-036 | Alur order tunai end-to-end | Member01 membeli 2 Cappuccino; tunai; bayar Rp100.000; meja `A-07`; lalu disetujui kasir, ready dan completed oleh dapur | Status berurutan pending cashier → processing → ready → completed; stok dan cashflow diperbarui | HTTP 201; status sesuai urutan; cashier tercatat; stok Kopi Arabica berkurang 20; `stock_deducted_at` terisi; cashflow penjualan terbentuk | Lulus |
| BB-037 | Total pemasukan hanya transaksi selesai | INV-001 completed Rp100.000; INV-002 pending cashier Rp999.999 | Widget hanya menghitung transaksi completed | `Total Pemasukan` aktual `Rp 100.000`; 5 statistik dihasilkan | Lulus |
| BB-038 | Menentukan dua best seller per kategori | Tiap kategori memiliki produk terjual sebanyak 10, 30, dan 20 pada transaksi completed | Dua produk dengan qty tertinggi ditandai populer | Produk nomor 2 dan 3 di tiap kategori ditandai; total 6 produk populer | Lulus |
| BB-039 | Tidak ada penjualan untuk best seller | Produk di seluruh kategori belum pernah dipesan | Tidak ada produk best seller | Jumlah `is_popular=true` menjadi 0 | Lulus |
| BB-040 | Label produk baru otomatis | Produk baru `Seasonal Matcha`; produk seed `Seed Cappuccino` dengan `is_new=false` | Produk baru berlabel baru; produk seed mengikuti nilai eksplisit | Seasonal Matcha `is_new=true`; Seed Cappuccino `false` | Lulus |

### E. Pembelian bahan baku dan data demo

| ID | Skenario | Data uji / aksi | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| BB-041 | Penerimaan pembelian menambah stok dan cashflow | PO-TEST-001; Supplier Test; Kopi Test stok 100; qty 5 × Rp10.000; status received | Stok menjadi 105 dan pengeluaran Rp50.000 tercatat | Stok aktual `105.00`; cashflow out kategori Pembelian Bahan Baku Rp50.000; catatan memuat supplier dan bahan | Lulus |
| BB-042 | Hitung ulang total pembelian dari item | Air: 10.000 × Rp5; Daging: 5.000 × Rp20; pajak Rp3 | Subtotal Rp150.000; total Rp150.003 | Nilai aktual subtotal 150000 dan total 150003 | Lulus |
| BB-043 | Menghapus pembelian received | PO-TEST-002 status `received` | Penghapusan ditolak | `ValidationException` terjadi sesuai aturan | Lulus |
| BB-044 | Menghapus pembelian draft | PO-TEST-003 status `draft` | Record terhapus | Record dengan invoice tersebut tidak ditemukan | Lulus |
| BB-045 | Kelengkapan dan idempotensi data demo | Jalankan MenuSeeder, IngredientSeeder, DemoScenarioSeeder; jalankan DemoScenarioSeeder kembali | Jumlah data sesuai skenario dan tidak bertambah saat diulang | 53 customer; 112 transaksi demo; 96 completed; 5 pending; 5 processing; 12 purchase; 4 retur jual; 3 retur beli; 8 komplain; 2 staf; >40 absensi; invoice unik; semua transaksi punya item; pemasukan > pengeluaran; jumlah tetap setelah seeding ulang | Lulus |

## 3. Skenario yang tidak dieksekusi

| ID | Fitur | Actual result | Alasan | Status |
|---|---|---|---|---|
| SKIP-01 | Membuat API token | Tidak dieksekusi | API support Jetstream tidak aktif | Skipped |
| SKIP-02 | Mengubah permission API token | Tidak dieksekusi | API support Jetstream tidak aktif | Skipped |
| SKIP-03 | Menghapus API token | Tidak dieksekusi | API support Jetstream tidak aktif | Skipped |
| SKIP-04 | Membuka verifikasi email | Tidak dieksekusi | Email verification tidak aktif | Skipped |
| SKIP-05 | Verifikasi email dengan hash valid | Tidak dieksekusi | Email verification tidak aktif | Skipped |
| SKIP-06 | Verifikasi email dengan hash salah | Tidak dieksekusi | Email verification tidak aktif | Skipped |
| SKIP-07 | Registrasi ketika fitur dinonaktifkan | Tidak dieksekusi | Pada konfigurasi saat ini registrasi aktif | Skipped |

## 4. Temuan dan batasan cakupan

1. Tidak ditemukan kegagalan pada 45 skenario bisnis yang dirangkum dari 40 feature test aktif. Satu feature test dapat mencakup beberapa skenario black-box, misalnya pemisahan akses tiga role dan alur status order.
2. Fitur API token dan verifikasi email belum dapat dinilai lulus/gagal karena memang dinonaktifkan oleh konfigurasi aplikasi.
3. Checkout yang terbukti aktual adalah metode tunai. Pembayaran Midtrans live, callback dari gateway nyata, kegagalan jaringan, signature tidak valid, dan refund gateway belum tercakup dalam suite.
4. Belum ada bukti pengujian browser nyata untuk tampilan responsif, tombol/menu JavaScript, kompatibilitas browser, aksesibilitas, dan visual layout.
5. Belum ada pengujian beban, keamanan penetrasi, race condition stok, atau transaksi paralel.

## 5. Bukti eksekusi ringkas

```text
Tests:    7 skipped, 41 passed (118 assertions)
Duration: 104.69s
Exit code: 0
```

