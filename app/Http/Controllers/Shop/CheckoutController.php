<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        // Checkout Anda berbasis modal di landing, jadi GET tidak perlu halaman khusus.
        // Karena route ini ada di group auth, redirect aman.
        return redirect()->route('home');
    }

    public function store(Request $request)
    {
        // Pastikan request ini memang JSON (fetch Anda sudah mengirim Accept JSON) :contentReference[oaicite:7]{index=7}
        if (!$request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'Endpoint ini hanya menerima request JSON.',
            ], 406);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $invoiceNo = $this->generateInvoiceNo();

            $items = collect($data['items'])
                ->map(fn ($x) => [
                    'product_id' => (int) $x['product_id'],
                    'qty' => (int) $x['qty'],
                ])
                ->values();

            $productIds = $items->pluck('product_id')->unique()->values();

            // Ambil produk dari DB agar harga tidak bisa dimanipulasi dari client
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            // Safety: jika ada product_id yang tidak ditemukan (misalnya race / deleted)
            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages([
                    'items' => ['Ada produk yang tidak ditemukan atau sudah dihapus. Silakan refresh menu.'],
                ]);
            }

            $subtotal = 0;
            $rows = [];

            foreach ($items as $row) {
                /** @var \App\Models\Product $product */
                $product = $products->get($row['product_id']);

                // Tolak produk nonaktif
                if ((bool) ($product->is_active ?? true) === false) {
                    throw ValidationException::withMessages([
                        'items' => ["Produk '{$product->name}' sedang nonaktif."],
                    ]);
                }

                $qty = $row['qty'];
                $price = (int) ($product->price ?? 0);

                if ($price < 0) {
                    throw ValidationException::withMessages([
                        'items' => ["Harga produk '{$product->name}' tidak valid."],
                    ]);
                }

                $lineSubtotal = $price * $qty;
                $subtotal += $lineSubtotal;

                $rows[] = [
                    'product_id' => (int) $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = 0;
            $tax = (int) floor(max(0, $subtotal - $discount) * 0.1);
            $total = max(0, ($subtotal - $discount) + $tax);

            $paidAmount = (int) ($data['paid_amount'] ?? 0);
            if ($data['payment_method'] !== 'cash') {
                $paidAmount = $total;
            }
            if ($paidAmount <= 0) {
                $paidAmount = $total;
            }
            $changeAmount = max(0, $paidAmount - $total);

            // Status: bayar sukses, lanjut persetujuan kasir
            $status = 'pending_cashier';

            $user = auth()->user();
            $customerId = $user?->customer?->id;

            $trx = Transaction::create([
                'invoice_no' => $invoiceNo,
                // customer_id: relasi customer diisi dari user yang login
                'customer_id' => $customerId,

                // cashier_id juga NULL (kasir akan punya flow sendiri via Filament)
                'cashier_id' => null,

                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,

                'payment_method' => $data['payment_method'],
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => $status,
            ]);

            // Insert item lebih efisien (bulk) dan konsisten
            $now = now();
            $insert = array_map(function ($it) use ($trx, $now) {
                return [
                    'transaction_id' => $trx->id,
                    'product_id' => $it['product_id'],
                    'qty' => $it['qty'],
                    'price' => $it['price'],
                    'subtotal' => $it['subtotal'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $rows);

            TransactionItem::insert($insert);

            return $trx->fresh()->load('items.product');
        });

        // Response format yang aman untuk fetch() Anda:
        // JS Anda memakai invoice_no / transaction_id / total :contentReference[oaicite:8]{index=8}
        return response()->json([
            'ok' => true,
            'message' => 'Checkout berhasil',
            'transaction_id' => $result->id,
            'invoice_no' => $result->invoice_no,
            'status' => $result->status,
            'subtotal' => $result->subtotal,
            'tax' => $result->tax,
            'total' => $result->total,
        ], 201);
    }

    private function generateInvoiceNo(): string
    {
        // Format: INV-YYMMDD-XXXXXX
        $date = now()->format('ymd');

        do {
            $rand = strtoupper(Str::random(6));
            $invoice = "INV-{$date}-{$rand}";
        } while (Transaction::where('invoice_no', $invoice)->exists());

        return $invoice;
    }
}
