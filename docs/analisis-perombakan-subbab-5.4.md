# Analisis Perombakan Subbab 5.4 Perancangan Model

## Ruang lingkup analisis

Analisis dilakukan terhadap route web, controller, Blade view, panel Filament, pembatasan role, dan seluruh artefak rancangan pada folder `docs`. Perancangan model pada Subbab 5.4 sebaiknya menjelaskan rancangan antarmuka/menu sebelum implementasi desain final di Figma. Karena itu, gambar disusun berdasarkan aktor dan alur kerja, bukan berdasarkan seluruh tabel atau seluruh halaman CRUD.

## 1. Baseline gambar perancangan yang tersedia

Repository menyimpan 32 rancangan HTML dan semuanya telah dirender menjadi PNG pada folder `docs/screenshots-mockup`.

### A. Wireframe awal

| No. | Judul gambar | File PNG | Keputusan |
|---|---|---|---|
| 1 | Wireframe Login Member | `login-wireframe.png` | Gabungkan dengan rancangan Login Member; gunakan satu versi final. |
| 2 | Wireframe Dashboard Admin | `dashboard-wireframe.png` | Pertahankan sebagai rancangan dashboard Admin/Owner. |
| 3 | Wireframe Menu Produk | `menu-produk-wireframe.png` | Revisi menjadi rancangan menu pelanggan/guest, bukan daftar produk admin. |
| 4 | Wireframe Halaman Transaksi | `transaksi-wireframe.png` | Pertahankan sebagai alur transaksi/checkout pelanggan. |
| 5 | Wireframe Pop-up Pembayaran | `pembayaran-wireframe.png` | Pertahankan dan gabungkan dalam penjelasan checkout serta Midtrans. |
| 6 | Wireframe Manajemen Pengguna | `manajemen-pengguna-wireframe.png` | Pertahankan khusus Admin; tidak berlaku untuk Owner. |
| 7 | Wireframe Halaman Laporan | `laporan-wireframe.png` | Pertahankan dan gabungkan dengan laporan periodik/absensi. |

### B. Rancangan input

| No. | Judul gambar | File PNG | Keputusan |
|---|---|---|---|
| 8 | Rancangan Input Login Member | `login-input-design.png` | Pertahankan; jadikan versi final pengganti wireframe login lama. |
| 9 | Rancangan Input Produk/Menu | `produk-input-design.png` | Pertahankan sebagai contoh form master data. |
| 10 | Rancangan Input Kategori | `kategori-input-design.png` | Hapus dari subbab utama; kategori di implementasi bukan menu Filament tersendiri. |
| 11 | Rancangan Input Bahan Baku | `bahan-baku-input-design.png` | Gabungkan dengan rancangan Produk/Menu sebagai kelompok master data. |
| 12 | Rancangan Input Supplier | `supplier-input-design.png` | Gabungkan dalam kelompok pembelian. |
| 13 | Rancangan Input Transaksi Penjualan | `transaksi-penjualan-input-design.png` | Revisi; transaksi pelanggan aktual dibuat melalui checkout, sedangkan form ini hanya relevan untuk admin. |
| 14 | Rancangan Input Pembelian Bahan Baku | `pembelian-bahan-baku-input-design.png` | Pertahankan sebagai proses pembelian. |
| 15 | Rancangan Input Retur Penjualan | `retur-penjualan-input-design.png` | Gabungkan dengan retur pembelian sebagai satu kelompok rancangan retur. |
| 16 | Rancangan Input Retur Pembelian | `retur-pembelian-input-design.png` | Gabungkan dengan retur penjualan. |
| 17 | Rancangan Input Pelanggan | `pelanggan-input-design.png` | Revisi menjadi pengelolaan akun pelanggan; resource pelanggan lama disembunyikan. |
| 18 | Rancangan Input Komplain Pelanggan | `komplain-pelanggan-input-design.png` | Revisi besar; pelanggan membuat komplain dari halaman Member, bukan form Create Filament. |
| 19 | Rancangan Input Staff | `staff-input-design.png` | Gabungkan dengan pengelolaan karyawan oleh Admin. |
| 20 | Rancangan Input Absensi | `absensi-input-design.png` | Revisi; input harian aktual dilakukan sendiri oleh Kasir/Dapur, sedangkan Filament untuk pengelolaan data. |

### C. Rancangan output

| No. | Judul gambar | File PNG | Keputusan |
|---|---|---|---|
| 21 | Rancangan Output Dashboard | `dashboard-output-design.png` | Pertahankan sebagai rancangan utama Admin/Owner. |
| 22 | Rancangan Output Data Produk | `data-produk-output-design.png` | Gabungkan sebagai satu contoh halaman daftar master data. |
| 23 | Rancangan Output Data Bahan Baku | `data-bahan-baku-output-design.png` | Gabungkan dengan master data dan tampilan cek stok Dapur. |
| 24 | Rancangan Output Data Supplier | `data-supplier-output-design.png` | Gabungkan ke kelompok pembelian. |
| 25 | Rancangan Output Detail Transaksi | `detail-transaksi-output-design.png` | Pertahankan sebagai representasi pengelolaan transaksi Admin/Owner. |
| 26 | Rancangan Output Pembelian Bahan Baku | `data-pembelian-bahan-baku-output-design.png` | Gabungkan dengan rancangan input pembelian. |
| 27 | Rancangan Output Data Retur | `data-retur-output-design.png` | Pertahankan sebagai ringkasan dua jenis retur. |
| 28 | Rancangan Output Data Staff | `data-staff-output-design.png` | Gabungkan dengan pengelolaan pengguna/karyawan. |
| 29 | Rancangan Output Data Absensi | `data-absensi-output-design.png` | Gabungkan dengan rancangan absensi dan laporan absensi. |
| 30 | Rancangan Output Data Komplain | `data-komplain-output-design.png` | Gabungkan dengan alur komplain pelanggan. |
| 31 | Rancangan Output Laporan Penjualan | `laporan-penjualan-output-design.png` | Pertahankan sebagai rancangan laporan periodik. |
| 32 | Rancangan Output Riwayat Pesanan Pelanggan | `riwayat-pesanan-pelanggan-output-design.png` | Pertahankan, tetapi judul sebaiknya menjadi “Rancangan Pesanan Aktif dan Detail Pesanan Member”. |

> Catatan: daftar di atas adalah baseline yang ditemukan di repository. Gambar yang benar-benar sedang ditempel pada naskah skripsi belum dapat diverifikasi tanpa file BAB V/PDF/DOCX skripsi. Jika urutan atau judul di naskah berbeda, file skripsi tetap perlu dilampirkan.

## 2. Struktur menu aktual berdasarkan pengguna

### A. Pelanggan/Guest

Guest membuka landing page pada `/`. Navigasi utama hanya menyediakan Login, sedangkan konten menyediakan Daftar Member, katalog menu, keranjang, dan checkout. Route `/menu` serta `/cart` memakai landing page yang sama dan navigasi bagian halaman dikelola oleh JavaScript.

Menu/fitur yang perlu muncul pada mockup guest:

- Beranda/landing page.
- Menu produk.
- Keranjang.
- Checkout.
- Login dan Daftar Member.
- Pemilihan pembayaran cash, QRIS, atau transfer.

### B. Member/User

Member menggunakan landing page yang sama dengan guest, tetapi navbar menampilkan menu profil. Konten tambahan menampilkan poin, total belanja, status member, dan tombol Lihat Pesanan.

Menu/fitur member:

- Beranda dan menu produk.
- Keranjang dan checkout.
- Member/membership.
- Pesanan aktif dan detail pesanan.
- Pengaturan profil.
- Pengajuan serta riwayat komplain.
- Logout.

### C. Kasir

Setelah login, role `cashier` atau `kasir` diarahkan ke `/cashier/orders`. Kasir tidak menggunakan sidebar Filament.

Menu aktual Kasir:

- Pesanan kasir/antrean persetujuan.
- Persetujuan pesanan untuk diteruskan ke dapur.
- Absensi.
- Logout.

Tidak ditemukan menu Riwayat khusus Kasir pada route atau view saat ini. Karena itu, mockup “Kasir: Pesanan dan Riwayat” tidak boleh digunakan kecuali fitur riwayat benar-benar ditambahkan ke program.

### D. Dapur

Role `kitchen` atau `dapur` diarahkan ke `/kitchen/orders` dan tidak menggunakan Filament.

Menu aktual Dapur:

- Board pesanan.
- Ubah status pesanan menjadi Siap dan Selesai.
- Cek stok bahan baku.
- Absensi.
- Logout.

### E. Admin dan Owner

Admin dan Owner sama-sama dapat masuk ke `/admin`. Keduanya melihat menu operasional utama, tetapi menu pengelolaan akun dibatasi hanya untuk Admin.

Kelompok sidebar Filament aktual:

| Kelompok | Menu | Admin | Owner |
|---|---|:---:|:---:|
| Dashboard | Dashboard | Ya | Ya |
| Master Data | Produk/Menu, Bahan Baku, Supplier | Ya | Ya |
| Manajemen Transaksi | Transaksi | Ya | Ya |
| Keuangan | Keuangan | Ya | Ya |
| Pembelian | Pembelian Bahan Baku | Ya | Ya |
| Retur | Retur Penjualan, Retur Pembelian | Ya | Ya |
| Layanan | Komplain | Ya | Ya |
| Manajemen Pengguna | Staff, Absensi | Ya | Ya |
| Laporan | Laporan Periodik, Laporan Absensi | Ya | Ya |
| Sistem | Audit Log | Ya | Ya |
| Pengguna | Data Admin, Data Owner, Data Karyawan, Data Pelanggan | Ya | Tidak |

`CustomerResource` lama dan `UserResource` generik masih memiliki route hasil discovery, tetapi disembunyikan dari navigasi dan seluruh method aksesnya mengembalikan `false`. Keduanya tidak boleh digambarkan sebagai menu sidebar.

## 3. Pemetaan UI ke route

| Aktor | Menu/halaman | Method dan route |
|---|---|---|
| Guest/Member | Landing | `GET /` |
| Guest/Member | Menu | `GET /menu` |
| Guest/Member | Keranjang | `GET /cart`, aksi `POST /cart/*` |
| Member | Checkout | `GET /checkout`, `POST /checkout` |
| Member | Membership | `GET /member` |
| Member | Pesanan aktif | `GET /member/orders/active` |
| Member | Detail/sinkronisasi pembayaran | `GET /orders/{transaction}`, `POST /orders/{transaction}/*` |
| Member | Komplain | `POST /member/complaints` |
| Kasir | Antrean pesanan | `GET /cashier/orders` |
| Kasir | Setujui pesanan | `POST /cashier/orders/{transaction}/approve` |
| Dapur | Board pesanan | `GET /kitchen/orders` |
| Dapur | Ubah status | `POST /kitchen/orders/{transaction}/ready|completed` |
| Dapur | Stok bahan | `GET /kitchen/ingredients` |
| Kasir/Dapur | Absensi | `GET /employee/attendance`, `POST /attendance/check-in|check-out` |
| Admin/Owner | Panel | `GET /admin` dan resource di bawah prefix `/admin` |

File route sumber: `routes/web.php`. API katalog produk berada pada `routes/api.php` dengan endpoint `GET /api/products`.

## 4. Rekomendasi struktur baru Subbab 5.4

Jumlah 32 gambar terlalu rinci untuk bagian perancangan menu dan menimbulkan pengulangan antara input, output, serta wireframe. Struktur yang disarankan adalah 12 gambar representatif berikut.

### 5.4 Perancangan Model Antarmuka

Paragraf pembuka menjelaskan bahwa rancangan model antarmuka dibuat sebagai acuan struktur navigasi, penempatan komponen, dan alur interaksi sebelum diterapkan sebagai high-fidelity design pada Figma.

#### 5.4.1 Rancangan Antarmuka Pelanggan dan Member

1. Gambar 5.x Rancangan Halaman Beranda dan Menu Guest.
2. Gambar 5.x Rancangan Login dan Registrasi Member.
3. Gambar 5.x Rancangan Keranjang, Checkout, dan Pembayaran.
4. Gambar 5.x Rancangan Dashboard Membership.
5. Gambar 5.x Rancangan Pesanan Aktif dan Detail Pesanan Member.

#### 5.4.2 Rancangan Antarmuka Kasir

6. Gambar 5.x Rancangan Antrean dan Persetujuan Pesanan Kasir.
7. Gambar 5.x Rancangan Absensi Karyawan.

#### 5.4.3 Rancangan Antarmuka Dapur

8. Gambar 5.x Rancangan Board Pesanan Dapur.
9. Gambar 5.x Rancangan Informasi Stok Bahan Baku Dapur.

#### 5.4.4 Rancangan Antarmuka Admin dan Owner

10. Gambar 5.x Rancangan Dashboard dan Navigasi Admin/Owner.
11. Gambar 5.x Rancangan Pengelolaan Data Operasional (master data, transaksi, pembelian, retur, komplain, staff, dan absensi).
12. Gambar 5.x Rancangan Laporan Periodik dan Laporan Absensi.

#### 5.4.5 Rancangan Khusus Administrator

13. Gambar 5.x Rancangan Pengelolaan Akun Admin, Owner, Karyawan, dan Pelanggan.

Jika batas jumlah gambar ketat, gambar nomor 10 dan 13 dapat digabung menjadi satu rancangan sidebar Admin lengkap dengan anotasi bahwa kelompok Pengguna tidak muncul pada Owner.

## 5. Screenshot program riil

Screenshot aktual telah diambil pada resolusi desktop yang konsisten dan disimpan dalam folder `docs/screenshots-aktual`:

1. `guest.png`: route `/`, memperlihatkan Login, hero, kategori, dan menu produk.
2. `member.png`: route `/home`, memperlihatkan profil, Member Benefits, Lihat Pesanan, dan menu produk.
3. `kasir.png`: route `/cashier/orders`, memperlihatkan menu Absensi, Logout, antrean, dan aksi Setujui & Kirim ke Dapur.
4. `dapur.png`: route `/kitchen/orders`, memperlihatkan menu Cek Stok, Absensi, Logout, dan Board Dapur.
5. `admin-filament.png`: route `/admin`, memperlihatkan dashboard serta sidebar Admin secara utuh, termasuk kelompok Pengguna.
6. `owner-filament.png`: route `/admin`, memperlihatkan dashboard dan sidebar Owner tanpa kelompok Pengguna.

Screenshot Admin dan Owner harus tetap dipisahkan dalam lampiran/bukti analisis karena menampilkan perbedaan hak akses. Dalam bagian mockup 5.4 keduanya boleh digabung menjadi satu gambar beranotasi bila perlu menghemat jumlah gambar.

Akun demo yang tersedia pada seeder:

| Role | Email | Password |
|---|---|---|
| Admin | `admin.demo@kefrec.test` | `DemoKefrec123!` |
| Owner | `owner.demo@kefrec.test` | `DemoKefrec123!` |
| Kasir | `kasir.demo@kefrec.test` | `DemoKefrec123!` |
| Dapur | `dapur.demo@kefrec.test` | `DemoKefrec123!` |
| Member | `member01@kefrec.test` | `Member123!` |

## 6. Kesimpulan perombakan

Subbab 5.4 lama perlu diubah dari kumpulan form input dan tabel output menjadi perancangan antarmuka berbasis aktor. Kategori tidak perlu menjadi gambar tersendiri karena tidak mempunyai menu khusus. Komplain dan absensi harus direvisi agar sesuai alur aktual: komplain dibuat oleh Member, sedangkan absensi dilakukan sendiri oleh Kasir/Dapur. Admin dan Owner juga tidak sepenuhnya identik karena hanya Admin yang memperoleh menu pengelolaan akun. Struktur baru lebih ringkas, sesuai route, dan dapat langsung dijadikan daftar frame saat membuat high-fidelity design di Figma.
