# BAB V - Class Diagram Sistem KEFREC

## 5.x Class Diagram

Class diagram menggambarkan struktur statis sistem KEFREC yang dibangun menggunakan pola Model-View-Controller (MVC) pada Laravel. Diagram memuat tiga kelompok class, yaitu **controller** sebagai pengatur alur permintaan pengguna, **service/support** sebagai penyedia layanan dan perhitungan bisnis, serta **model/entity** sebagai representasi data sekaligus penjaga aturan bisnis. Class bawaan framework, view Blade, dan class konfigurasi Filament tidak ditampilkan agar diagram berfokus pada proses utama aplikasi.

Struktur class diagram lengkap tersedia pada berkas `docs/class-diagram-bab-v.puml`. Diagram tersebut dapat dirender dengan PlantUML menjadi PNG atau SVG untuk dimasukkan ke laporan.

### 5.x.1 Struktur dan tanggung jawab class

#### A. Class controller

| Class | Method utama | Tanggung jawab |
|---|---|---|
| `CheckoutController` | `index()`, `store(request, midtrans)`, `generateInvoiceNo()` | Memvalidasi keranjang, menghitung subtotal/pajak/total, membuat transaksi beserta item, dan meminta token pembayaran. |
| `CartController` | `cart()`, `save()`, `index()`, `add()`, `update()`, `remove()`, `clear()` | Mengelola keranjang belanja pada session. |
| `OrderController` | `index()`, `show()`, `syncPayment()`, `cancelPayment()`, `authorizeCustomerOrder()`, `applyMidtransStatus()` | Menampilkan pesanan milik pelanggan serta menyinkronkan atau membatalkan pembayaran Midtrans. |
| `MidtransNotificationController` | `handle(request, midtrans)` | Memverifikasi callback Midtrans dan memperbarui status pembayaran/transaksi. |
| `OrderApprovalController` | `index()`, `approve()`, `assertCashier()` | Menampilkan antrean kasir dan mengubah pesanan dari `pending_cashier` menjadi `processing`. |
| `OrderBoardController` | `index()`, `markReady()`, `markCompleted()`, `assertKitchen()` | Mengatur proses dapur sampai pesanan selesai, kemudian memicu pengurangan stok dan pencatatan pemasukan. |
| `AttendanceSelfController` | `index()`, `employee()`, `checkIn()`, `checkOut()`, `isEmployeeRole()` | Mengelola absensi masuk dan keluar karyawan. |
| `MemberController` | `index()`, `activeOrders()`, `storeComplaint()` | Menampilkan profil/statistik member, pesanan aktif, dan menyimpan komplain. |

#### B. Class service dan support

| Class | Method utama | Tanggung jawab |
|---|---|---|
| `MidtransService` | `createSnapToken()`, `verifySignature()`, `getStatus()`, `cancel()` | Menjadi penghubung tunggal antara aplikasi dan API Midtrans. |
| `AuditLogger` | `log()` | Menyimpan aktivitas penting beserta pengguna, subject, metadata, IP, dan user agent. |
| `MemberStats` | `forCustomer()`, `empty()`, `pointsForTotal()`, `statusForPoints()` | Menghitung total belanja, poin, dan tingkatan member. |
| `BestSellerUpdater` | `sync()`, `bestSellerIdsForCategory()` | Menghitung produk terlaris dari transaksi selesai dan memperbarui penanda `is_popular`. |

#### C. Model dengan logika bisnis utama

| Class | Method utama | Analisis method |
|---|---|---|
| `Transaction` | `allowedStatusTransitions()`, `canTransition()`, `canTransitionTo()`, `booted()`, `generateInvoiceNo()`, `deductStockIfNeeded()`, `recordCashflowIfNeeded()` | Menjaga state machine transaksi, nomor invoice unik, idempotensi pengurangan stok, audit perubahan status, pembaruan produk terlaris, dan pencatatan pemasukan. |
| `Purchase` | `allowedStatusTransitions()`, `canTransition()`, `canTransitionTo()`, `booted()`, `generateInvoiceNo()`, `applyReceiptIfNeeded()`, `recalculateTotalsFromItems()`, `recordCashflowIfNeeded()`, `cashflowNote()` | Menjaga transisi pembelian, menghitung ulang nilai item, menambah stok bahan saat diterima, dan mencatat pengeluaran. |
| `SalesReturn` | `allowedStatusTransitions()`, `canTransition()`, `canTransitionTo()`, `booted()`, `generateReturnNo()`, `applyReturnIfNeeded()` | Memvalidasi status retur dan mengembalikan stok produk/bahan secara satu kali ketika retur diproses. |
| `PurchaseReturn` | `allowedStatusTransitions()`, `canTransition()`, `canTransitionTo()`, `booted()`, `generateReturnNo()`, `applyReturnIfNeeded()` | Memvalidasi status retur pembelian dan mengurangi kembali stok bahan baku secara satu kali. |
| `Complaint` | `customer()`, `handler()`, `allowedStatusTransitions()`, `canTransition()`, `canTransitionTo()`, `booted()` | Menghubungkan komplain dengan pelanggan/admin, memvalidasi perubahan status, dan membuat audit log. |
| `Product` | `booted()`, `ingredients()`, `transactionItems()`, `completedTransactionItems()` | Menetapkan produk baru dan menyediakan relasi resep serta data penjualan. |
| `User` | `canAccessPanel()`, `customer()`, `booted()`, `isEmployeeRole()` | Mengatur akses panel berdasarkan peran dan memastikan akun karyawan tidak mempunyai profil customer. |

Model lain lebih dominan sebagai entity dan pemilik relasi: `Customer`, `Category`, `Ingredient`, `Supplier`, `Staff`, `Attendance`, `TransactionItem`, `PurchaseItem`, `SalesReturnItem`, `PurchaseReturnItem`, `Cashflow`, dan `AuditLog`.

### 5.x.2 Relasi antarkelas

Relasi utama dalam sistem adalah sebagai berikut.

1. Satu `User` dapat mempunyai nol atau satu `Customer`, sedangkan satu `Customer` dapat mempunyai banyak `Transaction` dan `Complaint`.
2. Satu `Transaction` terdiri atas satu atau banyak `TransactionItem`; setiap item mengacu pada satu `Product`.
3. Satu `Category` memiliki banyak `Product`. `Product` dan `Ingredient` memiliki relasi many-to-many melalui tabel pivot resep yang menyimpan `qty` bahan per produk.
4. Satu `Supplier` memiliki banyak `Purchase`. Setiap `Purchase` terdiri atas banyak `PurchaseItem`, dan setiap item mengacu pada satu `Ingredient`.
5. `SalesReturn` mengacu pada transaksi dan pelanggan, serta memiliki banyak `SalesReturnItem` yang mengacu pada produk.
6. `PurchaseReturn` mengacu pada pembelian dan supplier, serta memiliki banyak `PurchaseReturnItem` yang mengacu pada bahan baku.
7. Satu `Staff` memiliki banyak `Attendance`.
8. `AuditLog` dimiliki oleh `User` dan menggunakan relasi polymorphic `subject()` sehingga dapat mencatat perubahan pada berbagai model.
9. `Cashflow` tidak memakai foreign key langsung, tetapi dibuat secara logis oleh transaksi selesai, pembelian diterima, retur penjualan, dan retur pembelian melalui atribut `source` dan `notes`.

### 5.x.3 Alur method bisnis penting

Alur penjualan dimulai dari `CheckoutController::store()`, yang membuat `Transaction` dan `TransactionItem`. Untuk pembayaran digital, method tersebut memanggil `MidtransService::createSnapToken()`. Status pembayaran diperbarui oleh `MidtransNotificationController::handle()` atau `OrderController::syncPayment()`. Kasir menjalankan `OrderApprovalController::approve()`, lalu dapur menjalankan `markReady()` dan `markCompleted()`. Pada tahap selesai, `Transaction::deductStockIfNeeded()` mengurangi bahan baku berdasarkan resep, sedangkan `recordCashflowIfNeeded()` mencatat pemasukan. Event model kemudian menjalankan audit dan `BestSellerUpdater::sync()`.

Alur pembelian dikendalikan oleh halaman resource Filament. Setelah data disimpan, `Purchase::recalculateTotalsFromItems()` menghitung subtotal dan total. Jika status menjadi `received`, `applyReceiptIfNeeded()` menambah stok bahan baku dan mencatat cashflow pengeluaran. Retur penjualan menggunakan `SalesReturn::applyReturnIfNeeded()` untuk mengembalikan stok, sedangkan retur pembelian menggunakan `PurchaseReturn::applyReturnIfNeeded()` untuk mengurangi stok bahan yang dikembalikan ke supplier.

Method perubahan stok menggunakan transaksi database, penguncian record (`lockForUpdate`), serta penanda waktu `stock_deducted_at`, `received_at`, atau `processed_at`. Mekanisme tersebut membuat proses bersifat idempotent, yaitu pemanggilan method berulang tidak menyebabkan stok berubah lebih dari satu kali.

### 5.x.4 Notasi diagram

- Tanda `+` menunjukkan method `public`.
- Tanda `#` menunjukkan method `protected`.
- Tanda `-` menunjukkan method `private`.
- Simbol `1`, `0..1`, dan `*` menunjukkan kardinalitas relasi.
- Garis penuh menunjukkan relasi data/asosiasi, sedangkan garis putus-putus menunjukkan dependency atau pemanggilan service/class lain.

