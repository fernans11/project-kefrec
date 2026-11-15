# Sistem Informasi Penjualan Pada Kefrec Coffeeshop

## Tujuan Dokumen

README ini adalah dokumen kerja lanjutan untuk menyelesaikan project skripsi **Sistem Informasi Penjualan Pada Kefrec Coffeeshop** sampai siap hosting sederhana pada **Senin, 29 Juni 2026**.

Dokumen ini disesuaikan dengan posisi project per **Jumat, 19 Juni 2026**. Fokus utama mulai dari titik ini bukan lagi membuat fitur besar dari nol, tetapi menyelesaikan validasi, merapikan bug kecil, menyiapkan bukti implementasi, menyusun pengujian black-box, membuat user manual, melakukan build production, smoke test, lalu mulai hosting.

Catatan ritme kerja:

- Senin sampai Sabtu boleh dipakai untuk deep work.
- Minggu adalah **full rest**.
- Tidak ada target coding, QA, screenshot, build, dokumentasi berat, atau hosting pada hari Minggu.
- Jadwal di bawah sengaja mengecualikan Minggu, 21 Juni 2026 dan Minggu, 28 Juni 2026.

---

## Ringkasan Project

Project ini adalah aplikasi Laravel untuk operasional penjualan makanan dan minuman di Kefrec Coffeeshop. Sistem mencakup registrasi pelanggan, login multi-role, menu, cart, checkout, payment gateway Midtrans Sandbox, approval kasir, board dapur, stok bahan baku, recipe produk, pembelian supplier, retur, komplain, cashflow, absensi staff, dashboard, dan laporan.

Metode skripsi:

- Metode perancangan: **Waterfall**
- Metode pengujian: **Black-box testing**

Stack utama:

- Laravel 12
- PHP 8.2+
- MySQL
- Filament 3
- Jetstream/Fortify
- Sanctum
- Midtrans PHP SDK
- Spatie Permission
- Vite dan Tailwind CSS

---

## Status Terkini Per 19 Juni 2026

Secara umum, project sudah berada di fase **finishing dan validasi akhir**. Fitur inti sudah ada dan cukup kuat untuk skenario demo, tetapi belum boleh dianggap siap hosting sebelum checklist QA, build, dokumentasi, dan data demo selesai.

### Sudah Berjalan di Kode

- Landing/menu customer.
- Register dan login customer.
- Redirect role setelah login.
- Panel admin/pemilik menggunakan Filament.
- Flow customer melihat menu, menambah cart, checkout, dan melihat pesanan.
- Pembayaran `cash`, `qris`, dan `transfer`.
- Integrasi Midtrans Sandbox untuk QRIS dan transfer bank.
- Webhook Midtrans di `/midtrans/notification`.
- Sinkronisasi manual status Midtrans melalui tombol cek status pembayaran.
- Pembatalan pesanan yang masih `pending_payment`.
- Kasir melihat pesanan `pending_cashier` dan melakukan approval.
- Dapur memproses pesanan dari `processing`, `ready`, sampai `completed`.
- Stok keluar saat transaksi selesai.
- Pembelian bahan baku sampai status `received`.
- Stok bahan baku masuk dari pembelian supplier.
- Cashflow masuk dari penjualan dan keluar dari pembelian.
- Retur penjualan dan retur pembelian.
- Komplain dari sisi admin.
- Absensi staff mandiri.
- Laporan absensi.
- Laporan periodik.
- Dashboard statistik, revenue chart, payment method pie, transaksi terbaru, cashflow, dan stok menipis.
- Seeder menu, bahan baku, recipe, dan absensi demo.

### Validasi Otomatis Yang Sudah Lolos

Test spesifik yang sudah berhasil dijalankan:

```bash
php artisan test tests/Feature/OverviewStatsTest.php
php artisan test tests/Feature/AdminIngredientPageTest.php
php artisan test tests/Feature/UserResourceRoleSeparationTest.php
```

Hasil:

- Overview stats hanya menghitung transaksi `completed`.
- Admin bisa membuka halaman bahan baku.
- Role internal dan pelanggan sudah mulai dipisahkan pada test resource user.
- Full test suite sudah berjalan tanpa gagal: 29 passed, 7 skipped, 58 assertions.

### Status Environment Lokal Terbaru

Catatan verifikasi per **Jumat, 19 Juni 2026**:

- MySQL lokal sudah aktif kembali setelah XAMPP dinyalakan.
- `php artisan migrate:status` sudah berhasil dan semua migration utama berstatus `Ran`.
- Data transaksi sudah terlihat di panel admin, sehingga koneksi aplikasi ke database lokal sudah pulih.
- Masalah kemarin terjadi karena XAMPP/MySQL belum aktif, sehingga browser/local storage dan backend database tidak tersambung saat aplikasi dicoba.

### Status Blocker Teknis

Blocker teknis hari Jumat sudah dibereskan dan project siap lanjut ke QA operasional.

Hasil verifikasi terbaru:

- `php artisan route:list` berhasil dan menampilkan 118 routes.
- `npm run build` berhasil dan menghasilkan asset di `public/build`.
- `php artisan test` berhasil: 29 passed, 7 skipped, 58 assertions.
- `AdminUserResource` dan `OwnerUserResource` sudah mengisi `usertype` otomatis saat create.
- `Product` model sudah memuat `stock` dan `track_stock` di `$fillable`.
- Komentar sisa `contentReference` di `CheckoutController` sudah dibersihkan.
- File sementara `temp_midtrans_test.php` sudah dihapus karena berisi konfigurasi Midtrans hardcoded.

---

## Aktor Sistem

| Aktor Skripsi | Role Aplikasi | Akses Utama | Fungsi Utama |
|---|---|---|---|
| Admin | `admin` | `/admin` | Mengelola master data, user, produk, stok, transaksi, pembelian, retur, komplain, keuangan, laporan, dan konfigurasi demo. |
| Pemilik | `owner` | `/admin` | Melihat dashboard, laporan, transaksi, cashflow, stok, dan aktivitas operasional. |
| Pelanggan | `user` atau `customer` | `/home` | Register, login, melihat menu, checkout, membayar, dan melihat status pesanan. |
| Kasir | `kasir` atau `cashier` | `/cashier/orders` | Memvalidasi pesanan dan meneruskan ke dapur. |
| Koki/Dapur | `dapur` atau `kitchen` | `/kitchen/orders` | Memproses pesanan, menandai siap, dan menyelesaikan pesanan. |

Catatan final role:

- Untuk demo, pilih satu penamaan role utama dan gunakan konsisten di data akun.
- Jika kode masih menerima dua versi seperti `kasir/cashier` dan `dapur/kitchen`, dokumentasikan sebagai kompatibilitas sementara.

---

## Scope Demo Utama

Skenario demo utama yang wajib stabil:

1. Pelanggan membuka landing/menu.
2. Pelanggan register atau login.
3. Pelanggan menambahkan produk ke cart.
4. Pelanggan checkout dengan `cash`.
5. Kasir approve pesanan cash.
6. Dapur memproses sampai `completed`.
7. Stok bahan baku berkurang.
8. Cashflow penjualan tercatat.
9. Dashboard dan laporan menampilkan data transaksi.
10. Pelanggan checkout dengan Midtrans Sandbox.
11. Pelanggan melakukan pembayaran QRIS atau transfer.
12. Status pembayaran disinkronkan.
13. Pesanan masuk ke kasir setelah pembayaran valid.

Skenario pendukung:

- Pembelian bahan baku dari supplier sampai `received`.
- Stok bahan baku bertambah setelah pembelian.
- Retur penjualan.
- Retur pembelian.
- Komplain dan tindak lanjut.
- Absensi masuk dan keluar.
- Laporan absensi.
- Laporan periodik dan cashflow.

---

## Jadwal Pasti Sampai Hosting

Jadwal ini dimulai dari **Jumat, 19 Juni 2026** dan berakhir pada **Senin, 29 Juni 2026**. Hari Minggu tidak dimasukkan sebagai hari kerja.

### Jumat, 19 Juni 2026 - Stabilkan Environment dan Bug Kecil

Target:

- Environment lokal kembali sehat.
- Bug kecil yang menghalangi QA dan build dibereskan.

Tugas teknis:

- [x] Nyalakan MySQL di XAMPP.
- [x] Pastikan database `project_kefrec` tersedia.
- [x] Jalankan `php artisan migrate:status`.
- [x] Database sudah siap, sehingga `php artisan migrate --seed` tidak perlu dijalankan ulang.
- [x] Jalankan `npm install` jika `vite` belum dikenali.
- [x] Jalankan `npm run build`.
- [x] Tambahkan `stock` dan `track_stock` ke `$fillable` model `Product`.
- [x] Pastikan create Admin otomatis menyimpan `usertype = admin`.
- [x] Pastikan create Owner otomatis menyimpan `usertype = owner`.
- [x] Bersihkan komentar sisa `contentReference` di `CheckoutController`.
- [x] Putuskan status `temp_midtrans_test.php`: dihapus sebelum hosting.
- [x] Jalankan test spesifik yang sudah ada.

Command acuan:

```bash
php artisan migrate:status
npm install
npm run build
php artisan test tests/Feature/OverviewStatsTest.php
php artisan test tests/Feature/AdminIngredientPageTest.php
php artisan test tests/Feature/UserResourceRoleSeparationTest.php
```

Output wajib:

- MySQL lokal bisa diakses. **Status: selesai, `migrate:status` berhasil pada 19 Juni 2026.**
- Build Vite berhasil atau penyebab gagalnya jelas. **Status: selesai, `public/build` sudah terbentuk pada 19 Juni 2026.**
- Bug role create dan product stock sudah dibereskan.
- Catatan error tersisa ditulis di daftar bug. **Status: tidak ada blocker teknis tersisa dari checklist Jumat.**

---

### Sabtu, 20 Juni 2026 - QA Operasional End-to-End

Target:

- Flow inti customer, kasir, dapur, stok, dan cashflow terbukti stabil.

Tugas QA:

- [x] Jalankan ulang flow customer dari menu sampai checkout cash.
- [x] Login kasir dan approve pesanan.
- [x] Login dapur dan proses sampai `completed`.
- [x] Catat stok bahan baku sebelum dan sesudah transaksi.
- [x] Pastikan stok bahan baku berkurang sesuai recipe.
- [x] Pastikan cashflow penjualan tercatat.
- [x] Jalankan checkout Midtrans QRIS.
- [x] Jalankan checkout Midtrans transfer.
- [x] Uji tombol `Cek Status Pembayaran`.
- [x] Uji pembatalan pesanan `pending_payment`.
- [ ] Jalankan pembelian bahan baku sampai `received`.
- [ ] Pastikan stok bahan baku bertambah.
- [ ] Pastikan cashflow pembelian tercatat sebagai pengeluaran.
- [ ] Uji retur penjualan.
- [ ] Uji retur pembelian.
- [ ] Uji komplain dan tindak lanjut.
- [ ] Uji absensi check-in dan check-out.
- [ ] Buka dashboard, laporan periodik, cashflow, dan laporan absensi.

Output wajib:

- Daftar hasil QA per fitur.
- Daftar bug prioritas tinggi, sedang, rendah.
- Batasan sistem yang perlu ditulis di skripsi.

Catatan hasil QA sementara:

- Flow customer dari menu sampai checkout berhasil tanpa kendala.
- Kasir berhasil login dan approve pesanan.
- Dapur berhasil login dan memproses pesanan sampai selesai.
- Customer dapat melihat perubahan status pesanan melalui fitur `Lihat Pesanan`.
- Saat transaksi selesai, status di Admin Panel tercatat `Selesai`.
- Pencatatan transaksi penjualan tampil di Admin Panel bagian transaksi dan di menu transaksi Midtrans.
- Checkout Midtrans QRIS berhasil sampai Payment Simulator.
- Checkout Midtrans Transfer berhasil sampai Payment Simulator.
- Tombol `Cek Status Pembayaran` berhasil mengubah status setelah pembayaran diselesaikan di Midtrans Payment Simulator.
- Tombol pembatalan pesanan berfungsi dan status pembatalan tercatat di Admin Panel serta Midtrans.
- Stok bahan baku berkurang sesuai dengan produk yang dipesan customer setelah transaksi selesai.
- Alur pembelian bahan baku dirapikan: supplier wajib dipilih agar setiap stok masuk punya asal pemasok yang jelas.
- Status pembelian dipakai sebagai alur stok: `Draf` belum menambah stok, `Diterima` menambah stok dan mencatat cashflow keluar, `Dibatalkan` tidak diproses.
- Catatan keuangan dari pembelian bahan baku dibuat lebih rinci: berisi nomor PO, supplier, dan daftar bahan baku yang dibeli.
- Perhitungan pembelian dirapikan: subtotal item dan total pembelian dihitung ulang dari item yang benar-benar tersimpan saat create/edit, sehingga daftar pembelian tidak lagi menyimpan total `0`, nilai pajak saja, atau angka sementara dari form.
- Jika pembelian berstatus `Diterima` diedit untuk memperbaiki total, catatan cashflow pembelian ikut disinkronkan ke total terbaru.
- Validasi otomatis pembelian ditambahkan: pembelian `Diterima` terbukti menambah stok bahan baku dan mencatat cashflow keluar.
- Validasi otomatis subtotal pembelian ditambahkan: contoh item Rp 50.000 + Rp 100.000 dengan pajak Rp 3 menghasilkan total Rp 150.003.
- Pembelian yang sudah `Diterima` tidak boleh dihapus agar stok dan cashflow yang sudah diproses tetap konsisten. Koreksi stok dilakukan melalui Retur Pembelian.

Template catatan bug:

```text
Kode Bug:
Fitur:
Langkah:
Hasil Aktual:
Hasil Seharusnya:
Prioritas: Tinggi/Sedang/Rendah
Status: Open/Fixed/Deferred
```

---

### Senin, 22 Juni 2026 - Screenshot Implementasi Customer, Kasir, dan Dapur

Target:

- Bukti visual BAB Implementasi untuk flow transaksi utama selesai.

Tugas:

- [ ] Screenshot landing/menu customer.
- [ ] Screenshot register customer.
- [ ] Screenshot login customer.
- [ ] Screenshot cart.
- [ ] Screenshot checkout.
- [ ] Screenshot pilihan pembayaran cash.
- [ ] Screenshot popup Midtrans Snap.
- [ ] Screenshot detail pesanan `pending_payment`.
- [ ] Screenshot tombol cek status pembayaran.
- [ ] Screenshot detail pesanan `pending_cashier`.
- [ ] Screenshot halaman kasir.
- [ ] Screenshot aksi approve kasir.
- [ ] Screenshot halaman dapur.
- [ ] Screenshot status `processing`.
- [ ] Screenshot status `ready`.
- [ ] Screenshot status `completed`.

Format nama file:

```text
01_customer_landing.png
02_customer_register.png
03_customer_login.png
04_customer_cart.png
05_customer_checkout.png
06_midtrans_snap.png
07_order_detail_pending_payment.png
08_cashier_order_approval.png
09_kitchen_processing.png
10_kitchen_completed.png
```

Output wajib:

- Screenshot customer, kasir, dan dapur tersimpan rapi.
- Alur demo utama bisa dijelaskan dari screenshot tanpa membuka aplikasi.

---

### Selasa, 23 Juni 2026 - Screenshot Implementasi Admin dan Laporan

Target:

- Bukti visual BAB Implementasi untuk admin/pemilik selesai.

Tugas:

- [ ] Screenshot dashboard admin.
- [ ] Screenshot statistik utama.
- [ ] Screenshot grafik pemasukan.
- [ ] Screenshot diagram metode pembayaran.
- [ ] Screenshot transaksi terbaru.
- [ ] Screenshot stok menipis.
- [ ] Screenshot produk/menu.
- [ ] Screenshot recipe bahan baku pada produk.
- [ ] Screenshot bahan baku.
- [ ] Screenshot supplier.
- [ ] Screenshot pembelian supplier.
- [ ] Screenshot transaksi.
- [ ] Screenshot cashflow/keuangan.
- [ ] Screenshot retur penjualan.
- [ ] Screenshot retur pembelian.
- [ ] Screenshot komplain.
- [ ] Screenshot staff.
- [ ] Screenshot absensi.
- [ ] Screenshot laporan periodik.
- [ ] Screenshot laporan absensi.
- [ ] Screenshot manajemen akun admin, owner, karyawan, dan pelanggan jika dipakai di skripsi.

Output wajib:

- Screenshot admin dan laporan tersimpan rapi.
- Fitur yang tidak ditampilkan di skripsi ditandai sebagai fitur pendukung atau batasan.

---

### Rabu, 24 Juni 2026 - Tabel Pengujian Black-Box

Target:

- Draft tabel pengujian black-box selesai dan sesuai fitur nyata di aplikasi.

Tugas:

- [ ] Buat tabel pengujian register pelanggan.
- [ ] Buat tabel pengujian login role.
- [ ] Buat tabel pengujian redirect role.
- [ ] Buat tabel pengujian menu customer.
- [ ] Buat tabel pengujian cart.
- [ ] Buat tabel pengujian checkout cash.
- [ ] Buat tabel pengujian checkout QRIS Midtrans.
- [ ] Buat tabel pengujian checkout transfer Midtrans.
- [ ] Buat tabel pengujian cek status pembayaran.
- [ ] Buat tabel pengujian pembatalan pesanan pending.
- [ ] Buat tabel pengujian approval kasir.
- [ ] Buat tabel pengujian proses dapur.
- [ ] Buat tabel pengujian stok keluar.
- [ ] Buat tabel pengujian pembelian supplier.
- [ ] Buat tabel pengujian stok masuk.
- [ ] Buat tabel pengujian cashflow masuk dan keluar.
- [ ] Buat tabel pengujian retur penjualan.
- [ ] Buat tabel pengujian retur pembelian.
- [ ] Buat tabel pengujian komplain.
- [ ] Buat tabel pengujian absensi.
- [ ] Buat tabel pengujian laporan periodik.
- [ ] Buat tabel pengujian laporan absensi.
- [ ] Buat tabel pengujian dashboard.

Format tabel:

| No | Fitur | Skenario | Data Uji | Hasil Yang Diharapkan | Hasil Aktual | Status |
|---|---|---|---|---|---|---|
| 1 | Login | User memasukkan email dan password benar | admin@kefrec.test / password | Masuk ke dashboard sesuai role | Sesuai | Berhasil |

Output wajib:

- Draft tabel black-box siap masuk bab pengujian.
- Setiap fitur penting minimal punya 1 skenario berhasil dan 1 skenario validasi/gagal jika relevan.

---

### Kamis, 25 Juni 2026 - User Manual dan Skenario Demo

Target:

- User manual ringkas dan alur demo presentasi selesai.

Tugas user manual:

- [ ] Tulis manual Admin.
- [ ] Tulis manual Owner.
- [ ] Tulis manual Customer.
- [ ] Tulis manual Kasir.
- [ ] Tulis manual Dapur.
- [ ] Tulis manual absensi staff.

Isi minimal setiap manual:

- Tujuan role.
- URL akses.
- Data login demo.
- Langkah utama.
- Hasil yang diharapkan.
- Catatan batasan.

Tugas skenario demo:

- [ ] Buat demo 10-15 menit.
- [ ] Tentukan urutan login akun.
- [ ] Tentukan produk yang dipakai untuk demo.
- [ ] Tentukan metode pembayaran yang ditampilkan.
- [ ] Tentukan data stok yang ditunjukkan.
- [ ] Tentukan laporan yang akan dibuka.
- [ ] Siapkan kalimat penjelasan singkat untuk Midtrans Sandbox dan webhook lokal.

Output wajib:

- User manual ringkas selesai.
- Skenario demo siap dipraktikkan tanpa improvisasi besar.

---

### Jumat, 26 Juni 2026 - Polishing Data Demo dan Backup

Target:

- Data demo bersih, konsisten, dan aman untuk presentasi.

Tugas:

- [ ] Rapikan akun demo.
- [ ] Pastikan password akun demo seragam atau dicatat jelas.
- [ ] Pastikan role akun demo sesuai akses.
- [ ] Rapikan produk/menu.
- [ ] Rapikan kategori menu.
- [ ] Rapikan harga menu.
- [ ] Rapikan gambar menu.
- [ ] Rapikan bahan baku.
- [ ] Rapikan recipe produk.
- [ ] Rapikan supplier.
- [ ] Rapikan staff.
- [ ] Rapikan customer.
- [ ] Bersihkan transaksi testing yang gagal atau tidak perlu.
- [ ] Sisakan beberapa transaksi contoh untuk dashboard dan laporan.
- [ ] Sisakan contoh pembelian supplier.
- [ ] Sisakan contoh retur dan komplain jika akan ditampilkan.
- [ ] Export/backup database demo.
- [ ] Simpan file backup dengan tanggal.

Nama backup:

```text
backup_project_kefrec_demo_2026_06_26.sql
```

Output wajib:

- Database demo rapi.
- Akun demo jelas.
- Backup database tersedia.

---

### Sabtu, 27 Juni 2026 - Build Production dan Smoke Test Final Lokal

Target:

- Project benar-benar siap dibawa ke hosting pada 29 Juni 2026.

Tugas build:

- [ ] Jalankan `composer install` jika vendor bermasalah.
- [ ] Jalankan `npm install` jika `node_modules` belum siap.
- [ ] Jalankan `npm run build`.
- [ ] Pastikan folder `public/build` tersedia.
- [ ] Jalankan `php artisan config:clear`.
- [ ] Jalankan `php artisan route:clear`.
- [ ] Jalankan `php artisan view:clear`.
- [ ] Jalankan `php artisan cache:clear`.

Tugas test:

- [ ] Jalankan test spesifik yang sudah lolos.
- [ ] Jalankan `php artisan test`.
- [ ] Jika full test masih timeout, catat test terakhir yang berjalan dan penyebab sementara.
- [ ] Jalankan `php artisan route:list` dan pastikan tidak timeout.
- [ ] Cek `storage/logs/laravel.log`.

Tugas smoke test manual:

- [ ] Buka landing.
- [ ] Login customer.
- [ ] Checkout cash.
- [ ] Login kasir.
- [ ] Approve pesanan.
- [ ] Login dapur.
- [ ] Selesaikan pesanan.
- [ ] Cek stok berkurang.
- [ ] Cek cashflow masuk.
- [ ] Login admin.
- [ ] Buka dashboard.
- [ ] Buka laporan periodik.
- [ ] Buka laporan absensi.
- [ ] Uji pembelian supplier singkat.
- [ ] Uji Midtrans singkat atau minimal cek Snap token masih terbentuk.

Command acuan:

```bash
composer install
npm install
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan route:list
php artisan test
```

Output wajib:

- Build production lokal berhasil.
- Smoke test final lokal berhasil.
- Daftar konfigurasi hosting siap.
- Bug tersisa sudah diberi keputusan: fix sebelum hosting atau dicatat sebagai batasan.

---

### Senin, 29 Juni 2026 - Mulai Hosting

Target:

- Aplikasi mulai tersedia di environment hosting/demo.

Tugas persiapan hosting:

- [ ] Tentukan media hosting: shared hosting, VPS, server kampus, atau lokal dengan tunnel.
- [ ] Pastikan PHP hosting minimal 8.2.
- [ ] Pastikan ekstensi PHP yang dibutuhkan aktif.
- [ ] Siapkan database MySQL hosting.
- [ ] Siapkan user dan password database hosting.
- [ ] Siapkan file `.env` production.
- [ ] Set `APP_ENV=production`.
- [ ] Set `APP_DEBUG=false`.
- [ ] Set `APP_URL` sesuai domain hosting.
- [ ] Set database hosting.
- [ ] Set konfigurasi Midtrans Sandbox atau production sesuai kebutuhan demo.
- [ ] Upload source code atau pull repository di server.
- [ ] Upload `public/build` dari hasil `npm run build`.
- [ ] Jalankan `composer install --no-dev --optimize-autoloader` jika server mendukung.
- [ ] Jalankan `php artisan key:generate` jika belum ada `APP_KEY`.
- [ ] Jalankan `php artisan migrate --force`.
- [ ] Import database demo jika memakai backup.
- [ ] Jalankan `php artisan storage:link` jika diperlukan.
- [ ] Jalankan cache production jika server stabil.
- [ ] Set document root hosting ke folder `public`.
- [ ] Set permission folder `storage` dan `bootstrap/cache`.
- [ ] Isi Payment Notification URL Midtrans jika memakai URL publik.

Command production acuan:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Payment Notification URL Midtrans:

```text
https://domain-hosting/midtrans/notification
```

Smoke test hosting:

- [ ] Buka halaman landing.
- [ ] Login admin.
- [ ] Login customer.
- [ ] Checkout cash.
- [ ] Login kasir.
- [ ] Login dapur.
- [ ] Buka dashboard.
- [ ] Buka laporan.
- [ ] Uji Midtrans jika domain publik sudah aktif.
- [ ] Cek log hosting.

Output wajib:

- Aplikasi bisa dibuka dari URL hosting.
- Login role utama berhasil.
- Flow transaksi dasar berhasil.
- Catatan konfigurasi hosting tersimpan.

---

## Definition of Done Sebelum Hosting

Project dianggap siap masuk hosting jika semua poin ini terpenuhi:

- [ ] MySQL lokal berjalan dan migration status bisa dicek.
- [ ] Data demo sudah rapi.
- [ ] Akun demo tersedia dan bisa login.
- [ ] Flow customer sampai checkout berjalan.
- [ ] Flow kasir approve berjalan.
- [ ] Flow dapur sampai completed berjalan.
- [ ] Cash berhasil diuji.
- [ ] QRIS Midtrans berhasil diuji atau batasannya dicatat.
- [ ] Transfer Midtrans berhasil diuji atau batasannya dicatat.
- [ ] Stok keluar berhasil divalidasi.
- [ ] Pembelian supplier dan stok masuk berhasil divalidasi.
- [ ] Cashflow masuk dan keluar berhasil divalidasi.
- [ ] Retur penjualan dan retur pembelian jelas statusnya.
- [ ] Komplain jelas statusnya.
- [ ] Absensi dan laporan absensi berjalan.
- [ ] Dashboard dan laporan periodik berjalan.
- [ ] Screenshot BAB Implementasi tersedia.
- [ ] Tabel black-box testing selesai.
- [ ] User manual ringkas selesai.
- [ ] `npm run build` berhasil.
- [ ] Smoke test final lokal berhasil.
- [ ] Backup database demo tersedia.

---

## Definition of Done Setelah Hosting

Project dianggap siap demo/presentasi setelah hosting jika:

- [ ] URL hosting bisa diakses.
- [ ] Login semua role demo berhasil.
- [ ] Asset CSS/JS tampil normal.
- [ ] Dashboard admin tampil.
- [ ] Flow transaksi cash berhasil di hosting.
- [ ] Flow Midtrans berhasil atau batasan sandbox/webhook dicatat.
- [ ] Database hosting berisi data demo rapi.
- [ ] Error log hosting tidak menunjukkan error fatal.
- [ ] Payment Notification URL Midtrans sudah diarahkan ke domain hosting jika memakai URL publik.
- [ ] File `.env` production tidak ikut terekspos publik.

---

## File Penting

### Route dan Controller

- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/Shop/CheckoutController.php`
- `app/Http/Controllers/Shop/OrderController.php`
- `app/Http/Controllers/MidtransNotificationController.php`
- `app/Http/Controllers/Cashier/OrderApprovalController.php`
- `app/Http/Controllers/Kitchen/OrderBoardController.php`
- `app/Http/Controllers/Attendance/AttendanceSelfController.php`

### Model Utama

- `app/Models/User.php`
- `app/Models/Customer.php`
- `app/Models/Product.php`
- `app/Models/Transaction.php`
- `app/Models/TransactionItem.php`
- `app/Models/Ingredient.php`
- `app/Models/Purchase.php`
- `app/Models/PurchaseReturn.php`
- `app/Models/SalesReturn.php`
- `app/Models/Cashflow.php`
- `app/Models/Complaint.php`
- `app/Models/Staff.php`
- `app/Models/Attendance.php`

### Filament

- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Pages/Dashboard.php`
- `app/Filament/Pages/PeriodicReport.php`
- `app/Filament/Pages/AttendanceReport.php`
- `app/Filament/Resources/ProductResource.php`
- `app/Filament/Resources/IngredientResource.php`
- `app/Filament/Resources/TransactionResource.php`
- `app/Filament/Resources/PurchaseResource.php`
- `app/Filament/Resources/CashflowResource.php`
- `app/Filament/Resources/AttendanceResource.php`
- `app/Filament/Widgets/OverviewStats.php`
- `app/Filament/Widgets/RevenueChart.php`
- `app/Filament/Widgets/PaymentMethodPie.php`
- `app/Filament/Widgets/LowStockIngredients.php`

### Payment Gateway

- `app/Services/MidtransService.php`
- `config/services.php`
- `.env`

Env Midtrans:

```text
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

---

## Akun Demo

Gunakan akun berikut untuk presentasi. Sesuaikan jika data aktual berbeda.

| Aktor | Role Teknis | Email | Password | Akses |
|---|---|---|---|---|
| Admin | `admin` | `admin@kefrec.test` | `password` | `/admin` |
| Pemilik | `owner` | `owner@kefrec.test` | `password` | `/admin` |
| Kasir | `kasir` | `cashier@kefrec.test` | `password` | `/cashier/orders` |
| Dapur | `dapur` | `kitchen@kefrec.test` | `password` | `/kitchen/orders` |
| Pelanggan | `user` | `customer@kefrec.test` | `password` | `/home` |

Catatan:

- Pastikan role di database sesuai dengan role yang dibaca kode.
- Jika memakai `cashier/kitchen`, pastikan controller dan redirect tetap menerima role tersebut.

---

## Setup Lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

Untuk Windows PowerShell, jika `copy` tidak sesuai, gunakan:

```powershell
Copy-Item .env.example .env
```

Build production lokal:

```bash
npm run build
php artisan config:clear
```

Test:

```bash
php artisan test
```

Test spesifik:

```bash
php artisan test tests/Feature/OverviewStatsTest.php
php artisan test tests/Feature/AdminIngredientPageTest.php
php artisan test tests/Feature/UserResourceRoleSeparationTest.php
```

---

## Catatan Hosting

Checklist hosting minimal:

- PHP 8.2 atau lebih baru.
- MySQL.
- Composer tersedia atau vendor diupload dari lokal.
- Document root diarahkan ke folder `public`.
- Folder `storage` writable.
- Folder `bootstrap/cache` writable.
- `.env` production tersedia.
- `APP_DEBUG=false`.
- `public/build` tersedia dari hasil Vite build.
- Database sudah migrate/import.
- Midtrans key sudah sesuai environment demo.
- Payment Notification URL diarahkan ke `/midtrans/notification` jika memakai domain publik.

---

## Batasan Sistem

- Sistem fokus pada pemesanan dan penjualan coffee shop satu outlet.
- Sistem tidak mencakup delivery.
- Cash diproses manual dan divalidasi kasir.
- QRIS dan transfer bank memakai Midtrans Sandbox untuk kebutuhan demo.
- Saat lokal, webhook Midtrans membutuhkan URL publik seperti ngrok.
- Tanpa ngrok, status Midtrans dapat disinkronkan dengan tombol `Cek Status Pembayaran`.
- Retur dan komplain dapat diposisikan sebagai pencatatan dan tindak lanjut jika tidak semua proses dibuat otomatis penuh.
- Stok produk non-olah dan stok bahan baku olahan harus dibedakan saat pengujian.
- Hosting yang dituju adalah hosting sederhana untuk demo, pengujian, dan pengumpulan skripsi.
