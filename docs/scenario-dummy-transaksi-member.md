# Skenario Data Dummy: 50 Member Transaksi Bersamaan

Dokumen ini berisi arahan untuk membuat skenario pengujian 50 member yang melakukan transaksi secara bersamaan pada project Kefrec.

## Tujuan

Dosen meminta dibuatkan data dummy berupa 50 member, lalu masing-masing member melakukan transaksi secara bersamaan untuk melihat apakah sistem mengalami bug atau error saat skenario dijalankan.

Pengujian ini berguna untuk menemukan masalah seperti:

- invoice transaksi duplicate
- stok produk atau bahan menjadi minus
- transaksi berhasil dibuat tetapi item transaksi gagal tersimpan
- cashflow tercatat dobel
- race condition saat update stok
- database deadlock
- transaksi gagal sebagian saat banyak user checkout bersamaan

## Konsep Utama

Jangan hanya menggunakan seeder biasa, karena seeder berjalan secara berurutan.

Seeder hanya cocok untuk membuat data awal. Untuk menguji 50 transaksi bersamaan, project perlu memiliki scenario runner yang menjalankan beberapa proses transaksi secara paralel.

Alur besar:

```text
1. Buat 50 user member
2. Buat 50 customer yang terhubung ke user
3. Pastikan ada produk/menu yang bisa dibeli
4. Jalankan 50 proses transaksi hampir bersamaan
5. Setiap member membuat transaksi sendiri
6. Sistem mencatat transactions dan transaction_items
7. Sistem mengurangi stok produk atau ingredient
8. Setelah selesai, cek apakah ada error
```

## Komponen Yang Disarankan

Buat tiga komponen Laravel:

```text
database/seeders/ScenarioMemberSeeder.php
app/Console/Commands/RunMemberTransactionScenario.php
app/Console/Commands/RunSingleMemberTransaction.php
```

## 1. ScenarioMemberSeeder

Tugas seeder ini adalah membuat data awal:

- 50 users dengan `usertype = user`
- 50 customers dengan `is_member = true`
- beberapa produk aktif
- stok produk atau bahan baku dibuat cukup banyak

Contoh akun dummy:

```text
member001@test.com
member002@test.com
member003@test.com
...
member050@test.com
```

Setiap user member harus punya customer:

```text
users.id -> customers.user_id
```

## 2. RunMemberTransactionScenario

Command ini menjadi command utama untuk menjalankan skenario.

Contoh pemakaian:

```bash
php artisan scenario:member-transactions --members=50
```

Tugas command ini:

- mengambil 50 customer member
- menjalankan 50 proses transaksi secara paralel
- setiap proses memanggil command transaksi tunggal
- menunggu semua proses selesai
- menampilkan ringkasan hasil

Contoh output yang diharapkan:

```text
=== Scenario: 50 Concurrent Member Transactions ===

Members: 50
Success transactions: 50
Failed transactions: 0
Transaction items created: 50
Duplicate invoices: 0
Products with negative stock: 0
Ingredients with negative stock: 0

Result: PASSED
```

## 3. RunSingleMemberTransaction

Command ini dijalankan untuk satu customer.

Contoh:

```bash
php artisan scenario:single-member-transaction --customer=1
php artisan scenario:single-member-transaction --customer=2
php artisan scenario:single-member-transaction --customer=3
```

Tugas command ini:

- memilih customer
- memilih produk yang akan dibeli
- membuat data `transactions`
- membuat data `transaction_items`
- menghitung subtotal, tax, dan total
- mengubah status transaksi menjadi `completed` atau status sesuai alur project
- menjalankan pengurangan stok
- mencatat cashflow jika diperlukan

## Kenapa Perlu Dua Command?

Kalau transaksi dibuat dengan loop biasa:

```php
foreach ($customers as $customer) {
    buatTransaksi($customer);
}
```

maka transaksi tetap berjalan berurutan, bukan bersamaan.

Untuk mensimulasikan transaksi bersamaan, command utama harus menjalankan banyak proses terpisah:

```text
Process 1  -> member 1 checkout
Process 2  -> member 2 checkout
Process 3  -> member 3 checkout
...
Process 50 -> member 50 checkout
```

Ini lebih mendekati kondisi nyata ketika banyak user checkout pada waktu yang sama.

## Data Dummy Yang Disarankan

Untuk pengujian awal, gunakan stok yang aman.

Contoh produk:

```text
Kopi Susu     stok 100
Americano     stok 100
Croissant     stok 100
```

Contoh ingredient:

```text
Kopi          stok 10000 gram
Susu          stok 10000 ml
Gula          stok 5000 gram
```

Setiap transaksi bisa membeli 1 sampai 3 produk.

## Level Skenario Pengujian

### Scenario 1: Normal Load

```text
50 member
masing-masing membeli 1 produk random
stok banyak
ekspektasi: semua transaksi berhasil
```

### Scenario 2: Same Product Race

```text
50 member
semua membeli produk yang sama
stok cukup, misalnya 100
ekspektasi: semua transaksi berhasil, stok berkurang tepat 50
```

### Scenario 3: Limited Stock

```text
50 member
semua membeli produk yang sama
stok hanya 20
ekspektasi: sistem tidak boleh membuat stok minus
```

Scenario ketiga penting untuk melihat apakah sistem sudah aman dari race condition.

## Validasi Setelah Scenario

Cek jumlah transaksi:

```sql
SELECT COUNT(*) FROM transactions;
```

Cek jumlah item transaksi:

```sql
SELECT COUNT(*) FROM transaction_items;
```

Cek invoice duplicate:

```sql
SELECT invoice_no, COUNT(*)
FROM transactions
GROUP BY invoice_no
HAVING COUNT(*) > 1;
```

Cek produk stok minus:

```sql
SELECT *
FROM products
WHERE stock < 0;
```

Cek ingredient stok minus:

```sql
SELECT *
FROM ingredients
WHERE stock < 0;
```

Cek transaksi tanpa item:

```sql
SELECT *
FROM transactions
WHERE id NOT IN (
    SELECT transaction_id FROM transaction_items
);
```

## Kesimpulan

Pendekatan yang paling rapi:

```text
Seeder dummy member
+ command simulasi concurrent transaction
+ validasi hasil setelah scenario
```

Contoh alur penggunaan akhir:

```bash
php artisan db:seed --class=ScenarioMemberSeeder
php artisan scenario:member-transactions --members=50
```

Dengan cara ini, pengujian bisa ditunjukkan ke dosen sebagai skenario 50 member yang melakukan transaksi bersamaan, lengkap dengan hasil sukses/gagal dan pengecekan bug.
