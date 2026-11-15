# BAB V - Entity Relationship Diagram Sistem KEFREC

## 5.x Entity Relationship Diagram

Entity Relationship Diagram (ERD) menggambarkan struktur data, atribut, primary key, foreign key, dan hubungan antartabel pada sistem KEFREC. Penyusunan ERD mengacu pada seluruh migration Laravel dan kemudian dicocokkan dengan relasi pada model Eloquent. Diagram utama berfokus pada tabel yang mendukung proses bisnis aplikasi; tabel internal framework seperti `cache`, `sessions`, `jobs`, `personal_access_tokens`, serta tabel otorisasi tidak ditampilkan agar diagram tetap mudah dibaca.

Sumber diagram terdapat pada berkas `docs/erd-bab-v.puml`. Diagram dapat dirender menjadi PNG atau SVG menggunakan PlantUML.

### 5.x.1 Kelompok entitas

| Kelompok | Entitas | Fungsi |
|---|---|---|
| Pengguna dan pelanggan | `users`, `customers` | Menyimpan akun sistem serta profil pelanggan/member. |
| Katalog dan persediaan | `categories`, `products`, `ingredients`, `ingredient_product` | Menyimpan menu, kategori, bahan baku, dan komposisi bahan setiap produk. |
| Penjualan | `transactions`, `transaction_items`, `sales_returns`, `sales_return_items` | Menyimpan transaksi penjualan, rincian produk, serta retur penjualan. |
| Pembelian | `suppliers`, `purchases`, `purchase_items`, `purchase_returns`, `purchase_return_items` | Menyimpan pemasok, pembelian bahan baku, dan retur pembelian. |
| Operasional | `staff`, `attendances`, `complaints`, `cashflows` | Mendukung absensi, penanganan komplain, dan arus kas. |
| Audit | `audit_logs` | Menyimpan jejak aktivitas pengguna terhadap berbagai objek bisnis. |

### 5.x.2 Entitas dan atribut utama

#### A. Pengguna dan pelanggan

1. **users** memiliki primary key `id`. Atribut utamanya adalah `name`, `username`, `email`, `phone`, `password`, dan `usertype`. Kolom `email` bersifat unik. Nilai `usertype` membedakan customer, kasir, dapur, admin, dan owner.
2. **customers** memiliki primary key `id` dan foreign key opsional `user_id`. Kolom tersebut bersifat unik sehingga satu akun hanya dapat mempunyai satu profil pelanggan. Data pelanggan meliputi `name`, `email`, `phone`, `address`, dan `is_member`.

#### B. Katalog dan persediaan

1. **categories** menyimpan kategori produk dengan `name` dan `slug` yang unik.
2. **products** menyimpan informasi produk, harga, status tampilan, rating, urutan, dan stok produk langsung. Kolom `category_id` dipakai sebagai hubungan konseptual menuju `categories`.
3. **ingredients** menyimpan bahan baku beserta satuan, stok, dan batas minimum stok.
4. **ingredient_product** merupakan associative entity antara produk dan bahan baku. Atribut `qty` menyatakan jumlah bahan yang diperlukan untuk satu produk. Kombinasi `product_id` dan `ingredient_id` bersifat unik.

#### C. Penjualan

1. **transactions** merupakan header transaksi. Entitas ini menyimpan nomor invoice, pelanggan, kasir, nilai transaksi, metode dan status pembayaran, data Midtrans, status proses pesanan, serta penanda pengurangan stok.
2. **transaction_items** merupakan detail transaksi yang mencatat produk, kuantitas, harga saat transaksi, dan subtotal. Penyimpanan harga pada tabel detail mempertahankan histori walaupun harga produk berubah.
3. **sales_returns** merupakan header retur penjualan yang dapat dihubungkan dengan transaksi dan pelanggan asal.
4. **sales_return_items** menyimpan produk dan jumlah barang yang dikembalikan pada setiap retur penjualan.

#### D. Pembelian

1. **suppliers** menyimpan identitas dan kontak pemasok.
2. **purchases** merupakan header pembelian bahan baku yang mencatat supplier, tanggal, nilai, status, dan waktu penerimaan.
3. **purchase_items** menyimpan bahan baku, kuantitas, harga beli, dan subtotal dari setiap pembelian.
4. **purchase_returns** merupakan header pengembalian bahan kepada supplier.
5. **purchase_return_items** menyimpan bahan baku dan kuantitas yang dikembalikan.

#### E. Operasional dan audit

1. **staff** menyimpan data pegawai operasional, sedangkan **attendances** mencatat waktu masuk dan keluar. Unique constraint `staff_id + date` mencegah absensi ganda pada hari yang sama.
2. **complaints** menyimpan komplain pelanggan dan pengguna yang menangani komplain melalui `handled_by`.
3. **cashflows** menyimpan arus kas masuk atau keluar. Hubungan dengan transaksi, pembelian, dan retur menggunakan `source` serta `notes`, bukan foreign key langsung.
4. **audit_logs** mempunyai foreign key `user_id` serta pasangan `subject_type` dan `subject_id`. Pasangan tersebut merupakan polymorphic reference sehingga satu tabel audit dapat menunjuk berbagai jenis objek bisnis.

### 5.x.3 Relasi dan kardinalitas

| No. | Relasi | Kardinalitas | Implementasi |
|---|---|---|---|
| 1 | `users` - `customers` | 1 : 0..1 | `customers.user_id`, unique, nullable, `ON DELETE SET NULL` |
| 2 | `categories` - `products` | 1 : 0..* | `products.category_id`; relasi konseptual, belum memiliki constraint FK |
| 3 | `products` - `ingredients` | M : N | Dijembatani `ingredient_product` dengan atribut `qty` |
| 4 | `customers` - `transactions` | 1 : 0..* | `transactions.customer_id`, nullable, `ON DELETE SET NULL` |
| 5 | `users` - `transactions` | 1 : 0..* | `transactions.cashier_id`, nullable, `ON DELETE SET NULL` |
| 6 | `transactions` - `transaction_items` | 1 : 0..* | Detail dihapus otomatis ketika transaksi dihapus |
| 7 | `products` - `transaction_items` | 1 : 0..* | Penghapusan produk dibatasi jika sudah dipakai transaksi |
| 8 | `transactions` - `sales_returns` | 1 : 0..* | `sales_returns.transaction_id`, nullable |
| 9 | `sales_returns` - `sales_return_items` | 1 : 0..* | Detail retur dihapus bersama header retur |
| 10 | `suppliers` - `purchases` | 1 : 0..* | `purchases.supplier_id`, nullable |
| 11 | `purchases` - `purchase_items` | 1 : 0..* | Detail pembelian dihapus bersama header pembelian |
| 12 | `ingredients` - `purchase_items` | 1 : 0..* | Setiap detail menunjuk satu bahan baku |
| 13 | `purchases` - `purchase_returns` | 1 : 0..* | `purchase_returns.purchase_id`, nullable |
| 14 | `purchase_returns` - `purchase_return_items` | 1 : 0..* | Detail retur dihapus bersama header retur |
| 15 | `staff` - `attendances` | 1 : 0..* | Absensi dihapus jika data staff dihapus |
| 16 | `customers` - `complaints` | 1 : 0..* | `complaints.customer_id`, nullable |
| 17 | `users` - `complaints` | 1 : 0..* | `complaints.handled_by`, nullable |
| 18 | `users` - `audit_logs` | 1 : 0..* | `audit_logs.user_id`, nullable |

### 5.x.4 Analisis integritas data

Integritas referensial pada tabel detail dijaga dengan foreign key dan `cascadeOnDelete`, khususnya pada item transaksi, pembelian, dan retur. Referensi pada header yang perlu mempertahankan histori menggunakan foreign key nullable dan `nullOnDelete`. Sebagai contoh, transaksi tetap tersimpan walaupun profil pelanggan dihapus, tetapi nilai `customer_id` menjadi `NULL`.

Nomor `invoice_no`, `return_no`, `midtrans_order_id`, email pengguna, slug produk/kategori, dan pasangan resep diberi unique constraint untuk mencegah duplikasi. Tabel absensi juga memakai unique constraint gabungan agar satu staff hanya mempunyai satu record per tanggal.

Terdapat tiga hubungan yang tidak memakai foreign key biasa. Pertama, `products.category_id` hanya memiliki index sehingga hubungannya dengan `categories` masih bersifat konseptual pada level aplikasi. Kedua, `audit_logs.subject_type` dan `subject_id` merupakan relasi polymorphic. Ketiga, `cashflows` mengacu pada sumber transaksi berdasarkan konvensi nilai `source` dan `notes`. Ketiganya ditampilkan menggunakan garis putus-putus pada ERD.

### 5.x.5 Notasi ERD

- `PK` menunjukkan primary key.
- `FK` menunjukkan foreign key.
- `UQ` menunjukkan unique constraint.
- `0..1` menunjukkan hubungan opsional maksimal satu record.
- `0..*` menunjukkan hubungan opsional dengan banyak record.
- Garis penuh menunjukkan foreign key database.
- Garis putus-putus menunjukkan hubungan konseptual atau polymorphic.

