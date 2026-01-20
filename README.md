# KeFrec Coffee Shop - Sistem Informasi Penjualan Berbasis Web (8-Day Completion Plan)

## Ringkasan
Project ini adalah Sistem Informasi Penjualan berbasis web untuk KeFrec Coffee Shop, mencakup proses pemesanan (dine-in), transaksi penjualan, manajemen stok, proses dapur, keuangan, laporan, dan manajemen SDM (absensi).  
Target penyelesaian fitur inti dan stabil dalam **8 hari**.

---

## Aktor & Hak Akses
Aktor yang digunakan sesuai perjanjian skripsi:
- **Admin**: konfigurasi sistem, master data, user management, audit ringan.
- **Pemilik (Owner)**: monitoring keseluruhan, laporan, keuangan, approval opsional.
- **Kasir**: transaksi, pembayaran, retur penjualan, komplain dasar.
- **Dapur**: antrian pesanan, proses memasak, update status pesanan.
- **Pelanggan**: pendaftaran, pemesanan, status pesanan, komplain.

> Catatan: kebutuhan "Member" pelanggan harus diputuskan apakah sama dengan "Pelanggan" atau role khusus (lihat bagian Klarifikasi).

---

## Status Progress Saat Ini
Sudah dikerjakan:
- [x] Landing Page (utama)
- [x] Admin Dashboard (selesai)
- [x] Daftar Member untuk pelanggan (role pelanggan)
- [x] Login Page
- [x] Register Page

Legenda:
- [x] Selesai
- [~] On Progress
- [ ] Belum

---

## Ruang Lingkup (Scope)
### Termasuk
- Pendaftaran pelanggan
- Penjualan minuman & makanan
- Stok barang **non-olah** (misal: botol minuman siap jual)
- Stok bahan baku **olah** (misal: kopi beans, susu untuk dibuat minuman)
- Pemesanan & antrian layanan (dine-in)
- Proses memasak oleh staff dapur
- Status pesanan
- Pembayaran: **QRIS**, **Tunai di kasir**, **Antar Bank**
- Pembelian bahan baku ke supplier
- Retur penjualan & pembelian
- Komplain
- Rekap pemasukan: diagram PIE
- Pemasukan & pengeluaran keuangan
- Absensi staff

### Tidak Termasuk (jika memang tidak dibuat)
- Pengantaran/kurir (delivery)
- Multi-outlet
- Integrasi payment gateway otomatis (opsional; jika QRIS hanya "konfirmasi manual", jelaskan)

---

## Definisi Selesai (Definition of Done / DoD)
Fitur dianggap selesai bila:
1. Halaman/flow berjalan tanpa error (happy path + validasi input dasar).
2. Role-based access berjalan (aktor hanya melihat menu yang sesuai).
3. Data tersimpan benar di database, relasi konsisten, dan tidak ada data "yatim".
4. Ada minimal 1 seed/dummy data untuk demo.
5. Ada 1-3 screenshot per fitur utama (untuk BAB Implementasi).

---

## Panduan Operasional Admin (Ringkas)
### A. Alur Harian Operasional
1. Login admin/owner ke `/admin`.
2. Cek kartu ringkas di Dashboard (pesanan, pemasukan, stok menipis).
3. Jika ada notifikasi stok menipis, buka `Master Data > Bahan Baku`.
4. Cek menu `Laporan > Laporan Periodik` untuk ringkasan harian.

### B. SOP Manajemen Stok
- Atur `Min Stok` pada setiap bahan baku (Master Data > Bahan Baku).
- Stok berkurang otomatis saat transaksi status `Selesai`.
- Stok bertambah otomatis saat pembelian status `Diterima`.
- Jika stok menipis, lakukan pembelian bahan baku ke supplier.

### C. SOP Transaksi & Status
Alur status: `Menunggu Kasir -> Diproses -> Siap Diambil -> Selesai`.
- Perubahan status hanya bisa mengikuti alur di atas.
- Transaksi selesai akan memicu pengurangan stok + pemasukan keuangan.

### D. SOP Pembelian Bahan Baku
1. Buat pembelian di `Pembelian > Pembelian Bahan Baku`.
2. Isi item dan total, lalu ubah status ke `Diterima`.
3. Stok bahan baku bertambah otomatis dan tercatat di Keuangan.

### E. SOP Retur
- Retur penjualan menambah stok kembali + pengeluaran keuangan.
- Retur pembelian mengurangi stok + pemasukan keuangan.
- Status retur hanya `Draf -> Diproses`.

### F. SOP Komplain
- Komplain ditindaklanjuti dengan status `Terbuka -> Diproses -> Selesai`.
- Catat tindak lanjut pada kolom `Tindak Lanjut`.

### G. Audit Log
- Menu `Sistem > Audit Log` menampilkan jejak aktivitas penting.
- Gunakan untuk validasi perubahan status dan troubleshooting.

### H. Laporan Periodik
- Menu `Laporan > Laporan Periodik`.
- Filter berdasarkan tanggal & status, lalu export CSV jika diperlukan.

---

## Checklist Fitur Sesuai Perjanjian Skripsi
### 1) Pendaftaran Pelanggan
- [x] Registrasi pelanggan
- [x] Kelola data pelanggan (Admin)
- [~] Status member (jika dipakai)

### 2) Kelola Penjualan Makanan & Minuman
- [x] Master Produk/Menu
- [~] Kategori menu
- [ ] Harga & promo/discount (jika ada)
- [x] Transaksi dine-in (Kasir)

### 3) Kelola Stok Barang (Non-Olah)
- [~] Master item stok non-olah
- [ ] Mutasi stok (masuk/keluar)
- [ ] Penyesuaian stok (opsional)

### 4) Kelola Stok Bahan Baku (Olah)
- [x] Master bahan baku
- [x] Recipe/BOM (produk -> bahan baku terpakai)
- [x] Pengurangan stok otomatis saat transaksi selesai (stock deducted)

### 5) Pemesanan & Antrian Layanan
- [x] Buat pesanan (Pelanggan / Kasir)
- [x] Queue/antrian
- [x] Nomor order/invoice

### 6) Proses Memasak oleh Dapur
- [x] Board antrian dapur
- [x] Status produksi (misal: menunggu -> diproses -> selesai)

### 7) Status Pesanan
- [x] Status: Draft / Menunggu Bayar / Dibayar / Diproses / Siap / Selesai / Batal
- [ ] Riwayat status (opsional)

### 8) Pembayaran (QRIS / Tunai / Antar Bank)
- [x] Pilih metode pembayaran
- [x] Simpan nominal bayar & kembalian (tunai)
- [ ] Bukti bayar / ref pembayaran (QRIS/Bank) - minimal input manual

### 9) Pembelian Bahan Baku ke Supplier
- [x] Master Supplier
- [x] Purchase order / pembelian
- [x] Penerimaan barang -> stok bertambah

### 10) Retur Penjualan & Pembelian
- [x] Retur penjualan -> stok kembali + penyesuaian keuangan
- [x] Retur pembelian -> stok berkurang + penyesuaian keuangan

### 11) Komplain
- [x] Form komplain pelanggan
- [x] Tindak lanjut komplain (Admin/Kasir/Pemilik)
- [x] Status komplain (Open/Progress/Closed)

### 12) Laporan Pemasukan (Diagram PIE)
- [x] Filter periode (harian/mingguan/bulanan)
- [x] Diagram PIE pemasukan (berdasarkan metode bayar / kategori / produk populer - pilih satu yang paling kuat)

### 13) Kelola Pemasukan & Pengeluaran Keuangan
- [x] Input pemasukan (non-penjualan jika ada)
- [x] Input pengeluaran (bahan baku, operasional)
- [x] Rekap saldo (opsional sederhana)

### 14) Absensi Staff
- [x] Master Staff
- [x] Absensi masuk/keluar
- [x] Rekap absensi per periode

---

## Rencana Penyelesaian 8 Hari (Sprint Plan)
> Fokus: selesaikan "alur inti" dulu: Produk -> Order -> Bayar -> Dapur -> Status -> Laporan, baru fitur pendukung.

### Day 1 - Finalisasi Role, Struktur Data, dan Admin Dashboard
**Target: pondasi siap**
- [ ] Final keputusan role "Member" (gabung Pelanggan atau role terpisah)
- [ ] Pastikan RBAC (Admin, Pemilik, Kasir, Dapur, Pelanggan)
- [ ] Rapikan Admin Dashboard: menu, navigasi, akses
- [ ] Susun/rapikan migration model inti: User, Product, Transaction, TransactionItem, Stock, dll

**DoD Day 1**: Admin dashboard tidak error, role menu rapi, semua model inti ada.

### Day 2 - Master Data: Produk, Kategori, Supplier, Staff
- [ ] CRUD Produk/Menu + upload foto (jika dipakai)
- [ ] CRUD Kategori
- [ ] CRUD Supplier
- [ ] CRUD Staff (untuk absensi)

**DoD Day 2**: Master data bisa dipakai transaksi.

### Day 3 - Order & Antrian (Dine-in)
- [ ] Buat pesanan (Kasir / Pelanggan)
- [ ] Antrian pesanan
- [ ] Draft transaksi + item transaksi

**DoD Day 3**: Pesanan terbentuk lengkap dengan itemnya.

### Day 4 - Pembayaran + Status Pesanan End-to-End
- [ ] Metode pembayaran (QRIS/Tunai/Bank)
- [ ] Status: menunggu bayar -> dibayar
- [ ] Cetak/nota sederhana (opsional)

**DoD Day 4**: Pesanan bisa dibayar dan berubah status.

### Day 5 - Dapur: Proses Memasak + Update Status
- [ ] Tampilan antrian dapur (role Dapur)
- [ ] Update status diproses -> siap -> selesai
- [ ] Sinkron status dengan Kasir/Pelanggan

**DoD Day 5**: Dapur bisa mengelola order sampai selesai.

### Day 6 - Stok (Non-Olah & Bahan Baku Olah) + Pembelian Supplier
- [ ] Stok non-olah (mutasi & keterkaitan transaksi bila relevan)
- [ ] Stok bahan baku olah + BOM/recipe (minimal sederhana)
- [ ] Pembelian ke supplier -> stok bertambah
- [ ] Stock deducted saat transaksi (dibayar/selesai) - pilih satu titik yang konsisten

**DoD Day 6**: Transaksi mempengaruhi stok sesuai desain.

### Day 7 - Retur + Komplain + Keuangan
- [ ] Retur penjualan & pembelian (minimal pencatatan + efek stok/keuangan)
- [ ] Komplain pelanggan + tindak lanjut
- [ ] Pemasukan & pengeluaran keuangan (rekap sederhana)

**DoD Day 7**: Fitur pendukung operasional berjalan.

### Day 8 - Laporan + Polishing + Demo Data + Dokumentasi
- [ ] Laporan pemasukan (PIE) + filter (filter periode)
- [ ] QA ringan (bugfix, validasi, permission)
- [ ] Seeder/demo data + akun demo tiap role
- [ ] Screenshot untuk BAB Implementasi

**DoD Day 8**: Siap demo dan siap ditulis untuk BAB VI.

---

## Akun Demo (Untuk Presentasi)
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@kefrec.test | password |
| Pemilik | owner@kefrec.test | password |
| Kasir | cashier@kefrec.test | password |
| Dapur | kitchen@kefrec.test | password |
| Pelanggan | customer@kefrec.test | password |

> Ganti sesuai kebutuhan Anda.

---

## Tech Stack
- Framework: Laravel
- Admin Panel: Filament v3
- Database: MySQL
- Auth: (Jetstream / Laravel Breeze / custom) - tulis yang dipakai
- Frontend: Blade / Livewire / (opsional)
- Chart: Chart.js / ApexCharts / (pilih salah satu)

---

## Setup Lokal
```bash
git clone <repo-url>
cd <project-folder>

composer install
cp .env.example .env
php artisan key:generate

# set DB creds in .env
php artisan migrate --seed

npm install
npm run dev

php artisan serve



