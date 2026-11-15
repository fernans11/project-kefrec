<?php

namespace Database\Seeders;

use App\Support\BestSellerUpdater;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoScenarioSeeder extends Seeder
{
    private CarbonImmutable $now;

    public function run(): void
    {
        $this->now = CarbonImmutable::now();

        DB::transaction(function (): void {
            $users = $this->seedUsers();
            $customers = $this->seedCustomers();
            $this->seedCategoriesAndProducts();
            $this->seedSuppliersAndPurchases();
            $transactions = $this->seedTransactions($users, $customers);
            $this->seedReturns($transactions, $customers);
            $this->seedComplaints($users, $customers);
            $this->seedStaffAndAttendances();
            $this->seedManualCashflows();
            $this->seedAuditLogs($users, $transactions);
        });

        BestSellerUpdater::sync();
    }

    private function seedUsers(): array
    {
        $accounts = [
            'admin' => ['Admin Demo', 'admin.demo@kefrec.test', 'admin'],
            'owner' => ['Pemilik Kefrec', 'owner.demo@kefrec.test', 'owner'],
            'cashier' => ['Siti Kasir', 'kasir.demo@kefrec.test', 'kasir'],
            'kitchen' => ['Budi Dapur', 'dapur.demo@kefrec.test', 'dapur'],
        ];
        $ids = [];

        foreach ($accounts as $key => [$name, $email, $role]) {
            $ids[$key] = $this->upsertId('users', ['email' => $email], [
                'name' => $name,
                'username' => Str::before($email, '@'),
                'phone' => '0812900000'.(count($ids) + 1),
                'usertype' => $role,
                'email_verified_at' => $this->now,
                'password' => Hash::make('DemoKefrec123!'),
            ]);
        }

        return $ids;
    }

    private function seedCustomers(): array
    {
        $firstNames = ['Andi', 'Ayu', 'Bagas', 'Bella', 'Cahyo', 'Citra', 'Dimas', 'Dinda', 'Eko', 'Fani', 'Galih', 'Gita', 'Hadi', 'Intan', 'Joko'];
        $lastNames = ['Pratama', 'Lestari', 'Saputra', 'Permata', 'Santoso', 'Maharani'];
        $ids = [];

        for ($i = 1; $i <= 50; $i++) {
            $email = sprintf('member%02d@kefrec.test', $i);
            $name = $firstNames[($i - 1) % count($firstNames)].' '.$lastNames[($i - 1) % count($lastNames)];
            $userId = $this->upsertId('users', ['email' => $email], [
                'name' => $name,
                'username' => sprintf('member%02d', $i),
                'phone' => sprintf('08131000%04d', $i),
                'usertype' => 'user',
                'email_verified_at' => $this->now,
                'password' => Hash::make('Member123!'),
            ]);
            $ids[] = $this->upsertId('customers', ['user_id' => $userId], [
                'name' => $name,
                'email' => $email,
                'phone' => sprintf('08131000%04d', $i),
                'address' => 'Jl. Demo Kefrec No. '.$i.', Jakarta',
                'is_member' => true,
            ]);
        }

        foreach ([['Walk-in Rina', '081399000001'], ['Walk-in Toni', '081399000002'], ['Komunitas Kopi Jakarta', '081399000003']] as [$name, $phone]) {
            $ids[] = $this->upsertId('customers', ['phone' => $phone], [
                'user_id' => null, 'name' => $name, 'email' => null,
                'address' => 'Jakarta', 'is_member' => false,
            ]);
        }

        return $ids;
    }

    private function seedCategoriesAndProducts(): void
    {
        $categories = [
            'Minuman' => ['minuman', 1], 'Makanan' => ['makanan', 2], 'Platters' => ['platters', 3],
        ];
        foreach ($categories as $name => [$slug, $order]) {
            $categoryId = $this->upsertId('categories', ['slug' => $slug], [
                'name' => $name, 'sort_order' => $order, 'is_active' => true,
            ]);
            DB::table('products')->where('category', $name)->update(['category_id' => $categoryId]);
        }

        $stocks = [
            'Cappuccino' => 80, 'Espresso' => 95, 'Latte' => 70, 'Americano' => 88,
            'Ice Coffee' => 55, 'Mocha' => 48, 'Croissant' => 35, 'Sandwich' => 22,
            'Muffin' => 6, 'Bagel' => 4, 'Platter Premium' => 18,
            'Platter Breakfast' => 15, 'Platter Snack' => 20,
        ];
        foreach ($stocks as $name => $stock) {
            DB::table('products')->where('name', $name)->update(['stock' => $stock, 'track_stock' => true]);
        }

        // Dua bahan sengaja berada di ambang minimum agar widget peringatan stok dapat didemokan.
        DB::table('ingredients')->where('name', 'Cream Cheese')->update(['stock' => 120, 'min_stock' => 150]);
        DB::table('ingredients')->where('name', 'Ragi')->update(['stock' => 90, 'min_stock' => 100]);
    }

    private function seedSuppliersAndPurchases(): void
    {
        $suppliers = [
            ['PT Aroma Nusantara', 'sales@aromanusantara.test', '081211110001', 'Bogor'],
            ['CV Susu Segar Jaya', 'order@sususegar.test', '081211110002', 'Bandung'],
            ['Toko Bahan Kue Makmur', 'halo@tokomakmur.test', '081211110003', 'Jakarta'],
            ['PT Pangan Sejuk', 'sales@pangansejuk.test', '081211110004', 'Tangerang'],
            ['Kebun Sayur Bersama', 'order@kebunsayur.test', '081211110005', 'Depok'],
        ];
        $supplierIds = [];
        foreach ($suppliers as [$name, $email, $phone, $city]) {
            $supplierIds[] = $this->upsertId('suppliers', ['email' => $email], [
                'name' => $name, 'phone' => $phone, 'address' => $city,
            ]);
        }

        $ingredients = DB::table('ingredients')->orderBy('id')->get();
        for ($i = 1; $i <= 12; $i++) {
            $date = $this->now->subDays(75 - ($i * 5));
            $status = $i === 11 ? 'draft' : ($i === 12 ? 'cancelled' : 'received');
            $rows = [];
            for ($j = 0; $j < 3; $j++) {
                $ingredient = $ingredients[($i + $j * 3) % $ingredients->count()];
                $qty = 250 + ($i * 25) + ($j * 100);
                $price = 35 + (($i + $j) % 5) * 10;
                $rows[] = [$ingredient, $qty, $price];
            }
            $subtotal = collect($rows)->sum(fn ($row) => (int) round($row[1] * $row[2]));
            $tax = (int) round($subtotal * .11);
            $invoice = sprintf('PO-DEMO-%s-%03d', $date->format('ymd'), $i);
            $purchaseId = $this->upsertId('purchases', ['invoice_no' => $invoice], [
                'supplier_id' => $supplierIds[($i - 1) % count($supplierIds)],
                'purchased_at' => $date->toDateString(), 'status' => $status,
                'subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax,
                'received_at' => $status === 'received' ? $date->addHours(5) : null,
                'notes' => $status === 'draft' ? 'Menunggu persetujuan pemilik' : ($status === 'cancelled' ? 'Dibatalkan karena jadwal kirim terlalu lama' : 'Pembelian rutin bahan baku'),
                'created_at' => $date,
            ]);
            DB::table('purchase_items')->where('purchase_id', $purchaseId)->delete();
            foreach ($rows as [$ingredient, $qty, $price]) {
                DB::table('purchase_items')->insert([
                    'purchase_id' => $purchaseId, 'ingredient_id' => $ingredient->id,
                    'qty' => $qty, 'price' => $price, 'subtotal' => (int) round($qty * $price),
                    'created_at' => $date, 'updated_at' => $date,
                ]);
            }
            if ($status === 'received') {
                $this->upsertCashflow('purchase', 'Pembelian '.$invoice, $date, 'out', 'Pembelian Bahan Baku', $subtotal + $tax);
            }
        }
    }

    private function seedTransactions(array $users, array $customers): array
    {
        $products = DB::table('products')->where('is_active', true)->orderBy('sort_order')->get();
        $transactions = [];

        for ($i = 1; $i <= 96; $i++) {
            $date = $this->now->subDays(59 - (($i - 1) % 60))->setTime(8 + ($i % 12), ($i * 7) % 60);
            $transactions[] = $this->makeTransaction(
                sprintf('INV-DEMO-%s-%03d', $date->format('ymd'), $i),
                $date, 'completed', $customers[($i - 1) % count($customers)], $users['cashier'],
                $products, $i, ['cash', 'qris', 'transfer'][$i % 3]
            );
        }

        // Antrean aktif dibuat cukup ramai untuk demo dan dibaca langsung oleh dashboard,
        // halaman kasir, serta board dapur dari tabel transaksi yang sama.
        $liveStatuses = [
            'draft',
            'pending_payment',
            'pending_cashier', 'pending_cashier', 'pending_cashier', 'pending_cashier', 'pending_cashier',
            'paid',
            'processing', 'processing', 'processing', 'processing', 'processing',
            'ready', 'ready',
            'cancelled',
        ];
        foreach ($liveStatuses as $index => $status) {
            $date = $this->now->subMinutes(($index + 1) * 13);
            $transactions[] = $this->makeTransaction(
                sprintf('INV-DEMO-LIVE-%02d', $index + 1), $date, $status,
                $customers[$index], $status === 'pending_cashier' ? null : $users['cashier'],
                $products, 200 + $index, $index % 2 ? 'qris' : 'cash'
            );
        }

        return $transactions;
    }

    private function makeTransaction(string $invoice, CarbonImmutable $date, string $status, int $customerId, ?int $cashierId, $products, int $seed, string $method): array
    {
        $itemCount = 1 + ($seed % 3);
        $items = [];
        for ($j = 0; $j < $itemCount; $j++) {
            $product = $products[($seed * 2 + $j * 3) % $products->count()];
            $qty = 1 + (($seed + $j) % 2);
            $items[] = [$product, $qty];
        }
        $subtotal = collect($items)->sum(fn ($row) => $row[0]->price * $row[1]);
        $discount = $status !== 'cancelled' && $seed % 5 === 0 ? (int) round($subtotal * .1) : 0;
        $tax = (int) floor(($subtotal - $discount) * .10);
        $total = $subtotal - $discount + $tax;
        $paid = in_array($status, ['completed', 'paid', 'processing', 'ready'], true);
        $online = in_array($method, ['qris', 'transfer'], true);
        $transactionId = $this->upsertId('transactions', ['invoice_no' => $invoice], [
            'midtrans_order_id' => $online ? 'MID-'.$invoice : null,
            'midtrans_transaction_id' => $online && $paid ? 'TRX-DEMO-'.str_pad((string) $seed, 4, '0', STR_PAD_LEFT) : null,
            'snap_token' => $online && ! $paid && $status !== 'cancelled' ? 'demo-snap-token-'.$seed : null,
            'customer_id' => $customerId, 'cashier_id' => $cashierId,
            'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $total,
            'payment_method' => $method,
            'payment_status' => $online ? ($paid ? 'settlement' : ($status === 'cancelled' ? 'cancel' : 'pending')) : ($paid ? 'paid' : 'unpaid'),
            'payment_type' => $online ? ($method === 'qris' ? 'qris' : 'bank_transfer') : 'cash',
            'payment_payload' => $online ? json_encode(['scenario' => 'demo', 'status' => $paid ? 'settlement' : 'pending']) : null,
            'paid_at' => $paid ? $date->addMinutes(4) : null,
            'paid_amount' => $paid ? ($method === 'cash' ? (int) ceil($total / 10000) * 10000 : $total) : 0,
            'change_amount' => $paid && $method === 'cash' ? ((int) ceil($total / 10000) * 10000) - $total : 0,
            'status' => $status,
            'stock_deducted_at' => $status === 'completed' ? $date->addMinutes(25) : null,
            'created_at' => $date,
        ]);
        DB::table('transaction_items')->where('transaction_id', $transactionId)->delete();
        foreach ($items as [$product, $qty]) {
            DB::table('transaction_items')->insert([
                'transaction_id' => $transactionId, 'product_id' => $product->id,
                'qty' => $qty, 'price' => $product->price, 'subtotal' => $product->price * $qty,
                'created_at' => $date, 'updated_at' => $date,
            ]);
        }
        if ($status === 'completed') {
            $this->upsertCashflow('sales', 'Penjualan '.$invoice, $date, 'in', 'Penjualan', $total);
        }

        return ['id' => $transactionId, 'invoice' => $invoice, 'customer_id' => $customerId, 'total' => $total, 'items' => $items, 'date' => $date, 'status' => $status];
    }

    private function seedReturns(array $transactions, array $customers): void
    {
        $completed = array_values(array_filter($transactions, fn ($transaction) => $transaction['status'] === 'completed'));
        foreach (['processed', 'draft', 'cancelled', 'processed'] as $i => $status) {
            $source = $completed[8 + ($i * 13)];
            [$product, $qty] = $source['items'][0];
            $returnNo = sprintf('RET-DEMO-%03d', $i + 1);
            $total = (int) $product->price;
            $returnId = $this->upsertId('sales_returns', ['return_no' => $returnNo], [
                'transaction_id' => $source['id'], 'customer_id' => $source['customer_id'],
                'status' => $status, 'subtotal' => $total, 'tax' => 0, 'total' => $total,
                'processed_at' => $status === 'processed' ? $source['date']->addDay() : null,
                'notes' => ['Minuman tumpah saat diserahkan', 'Menunggu pemeriksaan admin', 'Pengajuan tidak sesuai ketentuan', 'Produk tidak sesuai pesanan'][$i],
                'created_at' => $source['date']->addDay(),
            ]);
            DB::table('sales_return_items')->where('sales_return_id', $returnId)->delete();
            DB::table('sales_return_items')->insert([
                'sales_return_id' => $returnId, 'product_id' => $product->id, 'qty' => 1,
                'price' => $product->price, 'subtotal' => $total,
                'created_at' => $source['date']->addDay(), 'updated_at' => $source['date']->addDay(),
            ]);
            if ($status === 'processed') {
                $this->upsertCashflow('sales_return', 'Retur penjualan '.$returnNo, $source['date']->addDay(), 'out', 'Retur Penjualan', $total);
            }
        }

        $purchase = DB::table('purchases')->where('invoice_no', 'like', 'PO-DEMO-%')->where('status', 'received')->first();
        $purchaseItem = DB::table('purchase_items')->where('purchase_id', $purchase->id)->first();
        foreach (['processed', 'draft', 'cancelled'] as $i => $status) {
            $returnNo = sprintf('RET-PO-DEMO-%03d', $i + 1);
            $qty = 20 + ($i * 5);
            $total = (int) round($qty * $purchaseItem->price);
            $returnId = $this->upsertId('purchase_returns', ['return_no' => $returnNo], [
                'purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id,
                'status' => $status, 'subtotal' => $total, 'tax' => 0, 'total' => $total,
                'processed_at' => $status === 'processed' ? $this->now->subDays(20) : null,
                'notes' => ['Kemasan bahan baku rusak', 'Menunggu konfirmasi supplier', 'Retur dibatalkan setelah pengecekan ulang'][$i],
                'created_at' => $this->now->subDays(22 - $i),
            ]);
            DB::table('purchase_return_items')->where('purchase_return_id', $returnId)->delete();
            DB::table('purchase_return_items')->insert([
                'purchase_return_id' => $returnId, 'ingredient_id' => $purchaseItem->ingredient_id,
                'qty' => $qty, 'price' => $purchaseItem->price, 'subtotal' => $total,
                'created_at' => $this->now->subDays(22 - $i), 'updated_at' => $this->now->subDays(22 - $i),
            ]);
            if ($status === 'processed') {
                $this->upsertCashflow('purchase_return', 'Retur pembelian '.$returnNo, $this->now->subDays(20), 'in', 'Retur Pembelian', $total);
            }
        }
    }

    private function seedComplaints(array $users, array $customers): void
    {
        $cases = [
            ['open', 'Pesanan belum tampil', 'Pesanan saya belum terlihat di riwayat.', null],
            ['progress', 'Pembayaran sedang diverifikasi', 'Pembayaran QRIS berhasil tetapi status masih menunggu.', 'Bukti pembayaran sedang kami cocokkan.'],
            ['closed', 'Minuman kurang hangat', 'Latte yang diterima kurang hangat.', 'Produk sudah diganti dan pelanggan menerima penyelesaian.'],
            ['closed', 'Pelayanan sangat baik', 'Terima kasih untuk pelayanan staf hari ini.', 'Terima kasih atas apresiasinya.'],
            ['progress', 'Permintaan koreksi nomor telepon', 'Nomor telepon akun perlu diperbarui.', 'Admin sedang memverifikasi data akun.'],
            ['open', 'Saran menu tanpa gula', 'Mohon tersedia pilihan minuman tanpa gula.', null],
            ['closed', 'Item pesanan tertukar', 'Croissant saya tertukar dengan muffin.', 'Item yang benar telah dikirimkan.'],
            ['open', 'Struk tidak masuk email', 'Saya belum menerima struk digital.', null],
        ];
        foreach ($cases as $i => [$status, $title, $message, $response]) {
            $customerId = $customers[$i];
            $customer = DB::table('customers')->find($customerId);
            $this->upsertId('complaints', ['title' => '[DEMO] '.$title, 'customer_id' => $customerId], [
                'handled_by' => $status === 'open' ? null : $users['admin'],
                'name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone,
                'message' => $message, 'status' => $status, 'response' => $response,
                'created_at' => $this->now->subDays(14 - $i),
            ]);
        }
    }

    private function seedStaffAndAttendances(): void
    {
        // Hapus enam staff dummy lama; staff demo harus sama dengan akun Kasir dan Dapur.
        DB::table('staff')->whereIn('name', [
            'Andi Pratama', 'Siti Aulia', 'Budi Santoso', 'Maya Putri',
            'Rizky Hidayat', 'Nina Amelia', 'Fajar Ramadhan', 'Lina Marlina',
        ])->delete();

        $staff = [
            ['Siti Kasir', 'Kasir', '081290000003', true],
            ['Budi Dapur', 'Dapur', '081290000004', true],
        ];
        foreach ($staff as $index => [$name, $position, $phone, $active]) {
            $staffId = $this->upsertId('staff', ['name' => $name], [
                'phone' => $phone, 'position' => $position, 'is_active' => $active,
            ]);
            for ($day = 29; $day >= 0; $day--) {
                $date = $this->now->subDays($day);
                if ($date->isSunday()) {
                    continue;
                }
                $marker = ($day + $index * 3) % 19;
                $status = $marker === 0 ? 'izin' : ($marker === 7 ? 'sakit' : ($marker === 13 ? 'alpha' : 'hadir'));
                $late = ($day + $index) % 6 === 0 ? 18 : (($day * $index) % 8);
                $this->upsertId('attendances', ['staff_id' => $staffId, 'date' => $date->toDateString()], [
                    'clock_in' => $status === 'hadir' ? sprintf('08:%02d:00', $late) : null,
                    'clock_out' => $status === 'hadir' ? sprintf('17:%02d:00', ($day + $index) % 20) : null,
                    'status' => $status,
                    'notes' => $status === 'izin' ? 'Keperluan keluarga' : ($status === 'sakit' ? 'Surat dokter diterima' : ($status === 'alpha' ? 'Belum ada keterangan' : null)),
                ]);
            }
        }
    }

    private function seedManualCashflows(): void
    {
        $entries = [
            [28, 'out', 'Operasional', 850000, 'Pembayaran listrik dan air'],
            [21, 'out', 'Gaji', 6500000, 'Pembayaran gaji staf paruh bulan'],
            [16, 'out', 'Perawatan', 475000, 'Servis mesin espresso'],
            [10, 'in', 'Modal Tambahan', 8000000, 'Tambahan kas operasional dari pemilik'],
            [5, 'out', 'Promosi', 350000, 'Iklan media sosial'],
            [2, 'out', 'Operasional', 225000, 'Pembelian perlengkapan kebersihan'],
        ];
        foreach ($entries as [$days, $type, $category, $amount, $notes]) {
            $this->upsertCashflow('manual_demo', '[DEMO] '.$notes, $this->now->subDays($days), $type, $category, $amount);
        }
    }

    private function seedAuditLogs(array $users, array $transactions): void
    {
        DB::table('audit_logs')->where('description', 'like', '[DEMO]%')->delete();
        $actions = [
            ['login.success', 'User', $users['admin'], '[DEMO] Admin masuk ke panel'],
            ['product.updated', 'Product', DB::table('products')->value('id'), '[DEMO] Harga dan stok produk diperbarui'],
            ['purchase.received', 'Purchase', DB::table('purchases')->where('invoice_no', 'like', 'PO-DEMO-%')->value('id'), '[DEMO] Pembelian bahan baku diterima'],
            ['transaction.status_changed', 'Transaction', $transactions[0]['id'], '[DEMO] Pesanan diselesaikan dapur'],
            ['report.exported', null, null, '[DEMO] Pemilik mengunduh laporan periodik'],
        ];
        foreach ($actions as $i => [$action, $type, $subjectId, $description]) {
            DB::table('audit_logs')->insert([
                'user_id' => $i === 4 ? $users['owner'] : $users['admin'],
                'subject_type' => $type ? 'App\\Models\\'.$type : null, 'subject_id' => $subjectId,
                'action' => $action, 'description' => $description,
                'metadata' => json_encode(['scenario' => 'seminar_hasil']),
                'ip' => '127.0.0.1', 'user_agent' => 'Kefrec Demo Seeder',
                'created_at' => $this->now->subHours(5 - $i), 'updated_at' => $this->now->subHours(5 - $i),
            ]);
        }
    }

    private function upsertCashflow(string $source, string $notes, CarbonImmutable $date, string $type, string $category, int $amount): void
    {
        $this->upsertId('cashflows', ['source' => $source, 'notes' => $notes], [
            'date' => $date->toDateString(), 'type' => $type, 'category' => $category,
            'amount' => $amount, 'created_at' => $date,
        ]);
    }

    private function upsertId(string $table, array $identity, array $values): int
    {
        $existing = DB::table($table)->where($identity)->first();
        $timestamps = ['updated_at' => $this->now];
        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update(array_merge($values, $timestamps));
            return (int) $existing->id;
        }

        return (int) DB::table($table)->insertGetId(array_merge($identity, $values, [
            'created_at' => $values['created_at'] ?? $this->now,
            'updated_at' => $this->now,
        ]));
    }
}
