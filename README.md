# Sistem Informasi Penjualan Pada Kefrec Coffeeshop

## Tujuan Dokumen
README ini dipakai sebagai dokumen kerja lanjutan untuk menyelesaikan project skripsi **"Sistem Informasi Penjualan Pada Kefrec Coffeeshop"** sampai tahap hosting sederhana.

Posisi kerja saat dokumen ini disusun:
- Deep work utama tanggal **Rabu, 10 Juni 2026** sudah selesai.
- Flow inti customer, kasir, dan dapur sudah berjalan.
- Integrasi Midtrans Sandbox untuk QRIS dan transfer bank sudah selesai.
- Mulai dokumen ini, fokus kerja adalah sisa validasi operasional, dokumentasi skripsi, build, smoke test, dan persiapan hosting.

Target berikutnya:
- Mulai workflow aktif berikutnya: **Kamis, 11 Juni 2026**
- Mulai tahap hosting: **Senin, 29 Juni 2026**
- Mode launching: hosting sederhana, cukup stabil untuk demo, pengujian, dan pengumpulan skripsi.
- Catatan ritme kerja: **setiap hari Minggu adalah full rest**, tidak ada jadwal Deep Work Utama.

---

## Ringkasan Project
Project ini adalah aplikasi Laravel untuk operasional penjualan makanan dan minuman di Kefrec Coffeeshop. Sistem mencakup pendaftaran konsumen, pemesanan menu, checkout, payment gateway Midtrans, approval kasir, antrian dapur, stok, pembelian supplier, retur, komplain, cashflow, absensi staff, dashboard, dan laporan.

Metode yang dipakai dalam skripsi:
- Metode perancangan: **Waterfall**
- Metode pengujian: **Black-box testing**

---

## Aktor Sistem
| Aktor skripsi | Role aplikasi | Fungsi utama |
|---|---|---|
| Admin/Pemilik | `admin`, `owner` | Mengelola master data, produk, stok, transaksi, supplier, keuangan, laporan, dan monitoring. |
| Konsumen/Pelanggan | `customer` | Registrasi, login, melihat menu, checkout, membayar, dan melihat status pesanan. |
| Kasir | `cashier` | Memeriksa pesanan valid, approve pesanan, dan meneruskan ke dapur. |
| Koki/Dapur | `kitchen` | Memproses pesanan, menandai siap, dan menyelesaikan pesanan. |

Catatan:
- Member adalah konsumen/pelanggan yang sudah login.
- `owner` adalah representasi teknis untuk aktor Pemilik.
- `kitchen` adalah representasi teknis untuk aktor Koki/Dapur.

---

## Baseline Setelah Deep Work 10 Juni 2026
Bagian ini hanya mencatat posisi terakhir sebelum workflow aktif berikutnya dimulai. Detail pekerjaan selesai tidak dijadikan checklist harian.

Kondisi yang sudah menjadi baseline:
- Aplikasi berjalan lokal dengan Laravel 12 dan MySQL.
- Akun demo untuk role utama tersedia.
- Login dan redirect role sudah berjalan.
- Halaman customer, kasir, dapur, dan admin bisa dibuka.
- Customer bisa melihat menu, menambah cart, dan checkout.
- Cash berjalan manual dan masuk approval kasir.
- QRIS dan transfer bank berjalan melalui Midtrans Sandbox Snap.
- Status Midtrans memakai alur `pending_payment` lalu `pending_cashier` setelah pembayaran valid.
- Pesanan Midtrans tertunda bisa dicek ulang dan dibatalkan dari detail pesanan.
- Kasir bisa approve pesanan.
- Dapur bisa memproses sampai pesanan selesai.
- Pembelian bahan baku manual sampai `received` sudah tersedia.
- Absensi staff sudah tersedia.
- Validasi ulang pembayaran cash dan Midtrans sudah dilakukan.
- Skenario popup Snap ditutup, lanjut pembayaran, cek status pembayaran, dan pembatalan `pending_payment` sudah diuji.
- Validasi ulang flow customer sampai kasir dan dapur sudah dilakukan.
- Flow operasional penjualan sudah siap menjadi skenario demo utama.

Keputusan scope pembayaran:
- `cash`: manual, divalidasi kasir.
- `qris`: Midtrans Sandbox.
- `transfer`: Midtrans Sandbox, fokus BCA VA dan Mandiri Bill.
- Webhook otomatis Midtrans membutuhkan URL publik/ngrok jika aplikasi masih lokal.
- Saat lokal tanpa ngrok, gunakan tombol `Cek Status Pembayaran` pada detail pesanan.

---

## Fokus Kerja Berikutnya
Urutan prioritas setelah 12 Juni 2026:

1. Validasi stok keluar setelah transaksi selesai.
2. Validasi pembelian supplier, stok masuk, dan cashflow keluar.
3. Validasi retur penjualan dan retur pembelian.
4. Validasi komplain dan tindak lanjut.
5. Validasi dashboard, laporan, diagram pie metode pembayaran, cashflow, dan absensi.
6. Rapikan data demo.
7. Ambil screenshot BAB Implementasi.
8. Susun tabel pengujian black-box.
9. Susun user manual ringkas.
10. Build production lokal.
11. Smoke test final sebelum hosting.
12. Mulai tahap hosting pada 29 Juni 2026.

---

## Aturan Jadwal Deep Work
- Senin sampai Sabtu dapat dipakai untuk Deep Work Utama.
- Minggu adalah full rest.
- Pada hari Minggu tidak ada target coding, QA, screenshot, build, atau dokumentasi berat.
- Jika ada pekerjaan yang tertunda menjelang hari Minggu, pindahkan ke Senin berikutnya atau Sabtu sebelumnya.

---

## Workflow Harian Lanjutan
Workflow ini dimulai dari **Kamis, 11 Juni 2026** dan hanya berisi pekerjaan yang belum dilakukan setelah validasi Midtrans dan flow customer-kasir-dapur selesai.

### Hari 1 - Kamis, 11 Juni 2026
Target: validasi stok keluar setelah transaksi selesai.

Tugas:
- [ ] Pilih 1 produk olahan yang memakai bahan baku/recipe.
- [ ] Catat stok bahan baku sebelum transaksi.
- [ ] Lakukan order sampai status `completed`.
- [ ] Catat stok bahan baku setelah transaksi.
- [ ] Pastikan stok bahan baku berkurang sesuai recipe.
- [ ] Pilih 1 produk non-olah jika tersedia.
- [ ] Pastikan stok produk non-olah berkurang sesuai qty transaksi.
- [ ] Catat batasan jika data demo tidak memakai produk non-olah.

Output:
- Bukti validasi stok keluar untuk bahan baku olahan dan produk non-olah.

### Hari 2 - Jumat, 12 Juni 2026
Target: validasi pembelian supplier, stok masuk, dan cashflow keluar.

Tugas:
- [ ] Buat transaksi pembelian bahan baku.
- [ ] Isi item pembelian, qty, harga, subtotal, dan total.
- [ ] Ubah status pembelian menjadi `received`.
- [ ] Pastikan stok bahan baku bertambah.
- [ ] Pastikan cashflow pengeluaran tercatat.
- [ ] Uji pembelian barang/menu non-olah jika dipakai dalam data demo.
- [ ] Rapikan data supplier dan data bahan baku untuk demo.

Output:
- Alur pembelian supplier siap diuji black-box dan dijelaskan di skripsi.

### Hari 3 - Sabtu, 13 Juni 2026
Target: validasi retur penjualan.

Tugas:
- [ ] Ambil transaksi penjualan yang sudah selesai.
- [ ] Buat retur penjualan.
- [ ] Isi item retur dan nominal retur.
- [ ] Proses retur sampai status final.
- [ ] Periksa apakah retur mempengaruhi stok atau cashflow.
- [ ] Catat batasan jika retur hanya berupa pencatatan dan tindak lanjut.

Output:
- Status fitur retur penjualan jelas: otomatis penuh atau pencatatan.

### Minggu, 14 Juni 2026 - Full Rest
Tidak ada jadwal Deep Work Utama.

Catatan:
- Tidak ada target coding.
- Tidak ada target QA.
- Tidak ada target dokumentasi berat.
- Lanjutkan pekerjaan pada Senin, 15 Juni 2026.

### Hari 4 - Senin, 15 Juni 2026
Target: validasi retur pembelian.

Tugas:
- [ ] Ambil pembelian bahan baku/barang yang sudah `received`.
- [ ] Buat retur pembelian.
- [ ] Isi item retur dan nominal retur.
- [ ] Proses retur sampai status final.
- [ ] Periksa apakah retur mempengaruhi stok atau cashflow.
- [ ] Catat batasan jika retur hanya berupa pencatatan dan tindak lanjut.

Output:
- Status fitur retur pembelian jelas untuk dokumentasi dan pengujian.

### Hari 5 - Selasa, 16 Juni 2026
Target: validasi komplain dan tindak lanjut.

Tugas:
- [ ] Buat data komplain dari pelanggan/transaksi jika relasi tersedia.
- [ ] Isi isi komplain, status, dan tindak lanjut.
- [ ] Ubah status komplain sesuai alur.
- [ ] Pastikan Admin/Pemilik bisa melihat dan mengelola komplain.
- [ ] Catat apakah komplain tersedia dari sisi pelanggan atau hanya admin-side.

Output:
- Flow komplain siap masuk batasan sistem atau pengujian final.

### Hari 6 - Rabu, 17 Juni 2026
Target: validasi dashboard dan diagram metode pembayaran.

Tugas:
- [ ] Buka dashboard Admin/Pemilik.
- [ ] Pastikan statistik utama tampil.
- [ ] Pastikan chart revenue tampil.
- [ ] Pastikan diagram pie metode pembayaran tampil.
- [ ] Pastikan data cash, QRIS, dan transfer terbaca di laporan.
- [ ] Pastikan widget stok menipis tampil jika data tersedia.

Output:
- Dashboard siap untuk screenshot BAB Implementasi.

### Hari 7 - Kamis, 18 Juni 2026
Target: validasi laporan periodik dan cashflow.

Tugas:
- [ ] Uji filter laporan berdasarkan tanggal.
- [ ] Uji filter laporan berdasarkan status transaksi.
- [ ] Cocokkan pemasukan dengan transaksi `completed`.
- [ ] Cocokkan pengeluaran dengan pembelian `received`.
- [ ] Periksa kategori cashflow penjualan dan pembelian.
- [ ] Catat batasan laporan jika ada selisih data.

Output:
- Laporan periodik dan cashflow siap dijelaskan di skripsi.

### Hari 8 - Jumat, 19 Juni 2026
Target: validasi absensi staff dan laporan absensi.

Tugas:
- [ ] Uji absensi masuk.
- [ ] Uji absensi keluar.
- [ ] Buka laporan absensi.
- [ ] Pastikan data staff tampil.
- [ ] Pastikan filter periode berjalan.
- [ ] Catat hasil untuk black-box testing.

Output:
- Absensi dan laporan absensi siap screenshot.

### Hari 9 - Sabtu, 20 Juni 2026
Target: buffer QA operasional.

Tugas:
- [ ] Ulangi flow customer ke checkout ke kasir ke dapur.
- [ ] Ulangi flow pembayaran Midtrans dengan `Cek Status Pembayaran`.
- [ ] Ulangi flow pembelian bahan baku sampai `received`.
- [ ] Ulangi absensi staff.
- [ ] Buat daftar bug prioritas tinggi, sedang, rendah.
- [ ] Tetapkan batasan sistem final untuk fitur yang tidak otomatis penuh.

Output:
- Daftar bug dan batasan sistem siap dijadikan acuan minggu dokumentasi.

### Minggu, 21 Juni 2026 - Full Rest
Tidak ada jadwal Deep Work Utama.

Catatan:
- Tidak ada target coding.
- Tidak ada target QA.
- Tidak ada target dokumentasi berat.
- Lanjutkan pekerjaan pada Senin, 22 Juni 2026.

### Hari 10 - Senin, 22 Juni 2026
Target: screenshot BAB Implementasi bagian customer, kasir, dan dapur.

Tugas:
- [ ] Screenshot halaman menu pelanggan.
- [ ] Screenshot cart dan checkout.
- [ ] Screenshot popup Midtrans Snap.
- [ ] Screenshot detail status pesanan.
- [ ] Screenshot halaman persetujuan kasir.
- [ ] Screenshot board dapur.
- [ ] Simpan nama file screenshot dengan urutan fitur.

Output:
- Screenshot flow transaksi lengkap.

### Hari 11 - Selasa, 23 Juni 2026
Target: screenshot BAB Implementasi bagian admin.

Tugas:
- [ ] Screenshot dashboard Admin/Pemilik.
- [ ] Screenshot master produk/menu.
- [ ] Screenshot bahan baku dan stok.
- [ ] Screenshot supplier.
- [ ] Screenshot pembelian supplier.
- [ ] Screenshot transaksi.
- [ ] Screenshot cashflow.
- [ ] Screenshot absensi.
- [ ] Screenshot retur dan komplain jika dipakai.

Output:
- Screenshot admin lengkap.

### Hari 12 - Rabu, 24 Juni 2026
Target: tabel pengujian black-box.

Tugas:
- [ ] Buat tabel pengujian login role.
- [ ] Buat tabel pengujian pendaftaran konsumen.
- [ ] Buat tabel pengujian pemesanan pelanggan.
- [ ] Buat tabel pengujian pembayaran cash.
- [ ] Buat tabel pengujian pembayaran Midtrans.
- [ ] Buat tabel pengujian kasir.
- [ ] Buat tabel pengujian dapur.
- [ ] Buat tabel pengujian stok.
- [ ] Buat tabel pengujian pembelian supplier.
- [ ] Buat tabel pengujian retur.
- [ ] Buat tabel pengujian komplain.
- [ ] Buat tabel pengujian cashflow, absensi, dashboard, dan laporan.
Output:
- Draft tabel black-box testing selesai.

### Hari 13 - Kamis, 25 Juni 2026
Target: user manual dan skenario demo.

Tugas:
- [ ] Tulis user manual ringkas untuk Admin/Pemilik.
- [ ] Tulis user manual ringkas untuk Customer.
- [ ] Tulis user manual ringkas untuk Kasir.
- [ ] Tulis user manual ringkas untuk Dapur.
- [ ] Buat skenario demo 10-15 menit.
- [ ] Tulis batasan sistem, termasuk kebutuhan ngrok untuk webhook Midtrans lokal.

Output:
- User manual dan skenario demo selesai.

### Hari 14 - Jumat, 26 Juni 2026
Target: polishing data demo.

Tugas:
- [ ] Rapikan akun demo.
- [ ] Rapikan produk/menu.
- [ ] Rapikan bahan baku dan recipe.
- [ ] Rapikan supplier.
- [ ] Rapikan staff dan customer.
- [ ] Bersihkan transaksi testing yang tidak perlu.
- [ ] Siapkan backup database demo.

Output:
- Data demo bersih dan siap presentasi.

### Hari 15 - Sabtu, 27 Juni 2026
Target: build production lokal dan smoke test final sebelum hosting.

Tugas:
- [ ] Jalankan `npm run build`.
- [ ] Jalankan `php artisan test` jika memungkinkan.
- [ ] Jika test otomatis tidak relevan, catat sebagai risiko.
- [ ] Jalankan `php artisan config:clear`.
- [ ] Cek Laravel log.
- [ ] Buka landing/menu pelanggan.
- [ ] Login Admin/Pemilik.
- [ ] Login Kasir.
- [ ] Login Dapur.
- [ ] Login Customer.
- [ ] Uji checkout cash singkat.
- [ ] Uji checkout Midtrans singkat.
- [ ] Uji kasir approve.
- [ ] Uji dapur selesai.
- [ ] Uji dashboard dan laporan singkat.
- [ ] Siapkan daftar konfigurasi hosting.

Output:
- Build lokal dan smoke test final siap sebelum hosting.

### Minggu, 28 Juni 2026 - Full Rest
Tidak ada jadwal Deep Work Utama.

Catatan:
- Tidak ada target coding.
- Tidak ada target QA.
- Tidak ada target dokumentasi berat.
- Hari ini dipakai untuk istirahat penuh sebelum mulai tahap hosting.

### Hari 16 - Senin, 29 Juni 2026
Target: mulai tahap hosting.

Tugas:
- [ ] Lakukan review singkat data demo sebelum upload.
- [ ] Tentukan media hosting: shared hosting, VPS, server kampus, atau lokal presentasi dengan tunnel.
- [ ] Siapkan database hosting/demo.
- [ ] Siapkan file `.env` hosting.
- [ ] Set `APP_ENV=production`.
- [ ] Set `APP_DEBUG=false`.
- [ ] Set konfigurasi database hosting.
- [ ] Set konfigurasi Midtrans sesuai environment demo.
- [ ] Jika memakai server publik, isi Payment Notification URL Midtrans ke `/midtrans/notification`.
- [ ] Jalankan `npm run build` jika build belum tersedia.
- [ ] Upload source code atau pull repository di server.
- [ ] Upload hasil build asset jika build dilakukan lokal.
- [ ] Jalankan migration/seeder jika dibutuhkan.
- [ ] Jalankan smoke test awal di hosting.

Output:
- Aplikasi mulai tersedia di environment hosting dan siap validasi lanjutan.

---

## Definition of Done Sebelum Hosting
Project dianggap siap masuk tahap hosting jika:

1. Flow customer ke checkout ke kasir ke dapur stabil.
2. Cash, QRIS Midtrans, dan transfer Midtrans sudah diuji.
3. Stok keluar dan stok masuk sudah divalidasi atau batasannya dicatat.
4. Retur dan komplain sudah diuji atau batasannya dicatat.
5. Dashboard, cashflow, laporan, dan absensi sudah bisa ditampilkan.
6. Screenshot implementasi sudah tersedia.
7. Tabel black-box testing sudah disusun.
8. User manual ringkas sudah dibuat.
9. Data demo sudah dirapikan.
10. Build production lokal berhasil.
11. Smoke test final lokal berhasil.

---

## File Penting
### Route dan Controller
- Route utama: `routes/web.php`
- Redirect role: `app/Http/Controllers/HomeController.php`
- Checkout: `app/Http/Controllers/Shop/CheckoutController.php`
- Riwayat order: `app/Http/Controllers/Shop/OrderController.php`
- Webhook Midtrans: `app/Http/Controllers/MidtransNotificationController.php`
- Kasir: `app/Http/Controllers/Cashier/OrderApprovalController.php`
- Dapur: `app/Http/Controllers/Kitchen/OrderBoardController.php`
- Absensi mandiri: `app/Http/Controllers/Attendance/AttendanceSelfController.php`

### Model Utama
- User: `app/Models/User.php`
- Customer: `app/Models/Customer.php`
- Product: `app/Models/Product.php`
- Transaction: `app/Models/Transaction.php`
- TransactionItem: `app/Models/TransactionItem.php`
- Ingredient: `app/Models/Ingredient.php`
- Purchase: `app/Models/Purchase.php`
- Cashflow: `app/Models/Cashflow.php`
- Complaint: `app/Models/Complaint.php`
- Staff: `app/Models/Staff.php`
- Attendance: `app/Models/Attendance.php`

### Payment Gateway
- Service Midtrans: `app/Services/MidtransService.php`
- Konfigurasi service: `config/services.php`
- Env Midtrans:
  - `MIDTRANS_SERVER_KEY`
  - `MIDTRANS_CLIENT_KEY`
  - `MIDTRANS_IS_PRODUCTION=false`
  - `MIDTRANS_IS_SANITIZED=true`
  - `MIDTRANS_IS_3DS=true`

---

## Akun Demo
Gunakan akun berikut untuk presentasi. Sesuaikan jika akun aktual berbeda.

| Aktor | Role teknis | Email | Password | Akses |
|---|---|---|---|---|
| Admin/Pemilik | Admin | admin@kefrec.test | password | `/admin` |
| Admin/Pemilik | Owner | owner@kefrec.test | password | `/admin` |
| Kasir | Cashier | cashier@kefrec.test | password | `/cashier/orders` |
| Koki/Dapur | Kitchen | kitchen@kefrec.test | password | `/kitchen/orders` |
| Konsumen/Pelanggan | Customer | customer@kefrec.test | password | `/home` |

---

## Setup Lokal
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

Untuk build production lokal:

```bash
npm run build
php artisan config:clear
```

Untuk test:

```bash
php artisan test
```

---

## Catatan Hosting
Checklist minimal hosting:

- Gunakan PHP 8.2 atau lebih baru.
- Gunakan database MySQL.
- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Jalankan `composer install --no-dev --optimize-autoloader` jika server mendukung Composer.
- Upload folder `public/build` dari hasil `npm run build`.
- Set konfigurasi database di `.env`.
- Set konfigurasi Midtrans di `.env`.
- Jalankan `php artisan migrate --force`.
- Jalankan `php artisan storage:link` jika upload file dipakai.
- Isi Payment Notification URL Midtrans ke:

```text
https://domain-hosting/midtrans/notification
```

Optimasi Laravel jika server mendukung:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Batasan Sistem
- Sistem fokus pada pemesanan dan penjualan di coffee shop, bukan delivery.
- Sistem hanya untuk satu outlet.
- Cash diproses manual dan divalidasi kasir.
- QRIS dan transfer bank memakai Midtrans Sandbox.
- Saat lokal, webhook Midtrans membutuhkan ngrok agar otomatis masuk ke aplikasi.
- Tanpa ngrok, status Midtrans bisa disinkronkan lewat tombol `Cek Status Pembayaran`.
- E-wallet di luar channel Snap aktif tidak menjadi prioritas demo.
- Retur dan komplain dapat diposisikan sebagai pencatatan dan tindak lanjut jika efek otomatis belum penuh.
- Stok produk non-olah dan stok bahan baku olahan harus dibedakan saat pengujian.
