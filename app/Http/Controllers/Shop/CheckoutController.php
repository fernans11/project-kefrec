<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\MidtransService;
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

    public function store(Request $request, MidtransService $midtrans)
    {
        // Pastikan request ini memang JSON (fetch Anda sudah mengirim Accept JSON).
        if (!$request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'Endpoint ini hanya menerima request JSON.',
            ], 406);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:25'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
        ], [
            'table_number.required' => 'Nomor meja wajib diisi.',
            'table_number.max' => 'Nomor meja maksimal 25 karakter.',
            'customer_name.max' => 'Nama pelanggan maksimal 100 karakter.',
        ]);

        $isMidtransPayment = in_array($data['payment_method'], ['qris', 'transfer'], true);
        if ($isMidtransPayment && ! $request->user()) {
            return response()->json([
                'ok' => false,
                'message' => 'Silakan login terlebih dahulu untuk pembayaran QRIS atau Transfer Bank.',
                'redirect_url' => route('login'),
            ], 401);
        }

        if ($data['payment_method'] === 'cash' && ! $request->user() && trim((string) ($data['customer_name'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'customer_name' => ['Nama pelanggan wajib diisi untuk checkout tunai tanpa login.'],
            ]);
        }

        $result = DB::transaction(function () use ($data, $request) {
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

            $isMidtransPayment = in_array($data['payment_method'], ['qris', 'transfer'], true);
            $paidAmount = (int) ($data['paid_amount'] ?? 0);
            if ($isMidtransPayment) {
                $paidAmount = 0;
            } elseif ($paidAmount <= 0) {
                $paidAmount = $total;
            }
            $changeAmount = max(0, $paidAmount - $total);

            $status = $isMidtransPayment ? 'pending_payment' : 'pending_cashier';

            $user = $request->user();
            $customerId = $user?->customer?->id;
            $guestName = trim((string) ($data['customer_name'] ?? ''));
            $tableNumber = trim((string) ($data['table_number'] ?? ''));

            $trx = Transaction::create([
                'invoice_no' => $invoiceNo,
                'midtrans_order_id' => $isMidtransPayment ? $invoiceNo : null,
                // customer_id: relasi customer diisi dari user yang login
                'customer_id' => $customerId,

                // cashier_id juga NULL (kasir akan punya flow sendiri via Filament)
                'cashier_id' => null,

                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,

                'payment_method' => $data['payment_method'],
                'payment_status' => $isMidtransPayment ? 'pending' : 'manual',
                'payment_payload' => [
                    'customer_type' => $user ? 'member' : 'guest',
                    'customer_name' => $user?->name ?? $guestName,
                    'table_number' => $tableNumber,
                ],
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

        if (in_array($result->payment_method, ['qris', 'transfer'], true)) {
            try {
                $snapToken = $midtrans->createSnapToken($result);
                $result->update(['snap_token' => $snapToken]);
                $result->snap_token = $snapToken;
            } catch (\Throwable $exception) {
                $result->update([
                    'status' => 'cancelled',
                    'payment_status' => 'snap_failed',
                    'payment_payload' => array_merge($result->payment_payload ?? [], [
                        'message' => $exception->getMessage(),
                    ]),
                ]);

                return response()->json([
                    'ok' => false,
                    'message' => 'Gagal membuat pembayaran Midtrans: ' . $exception->getMessage(),
                ], 422);
            }
        }

        // Response format yang aman untuk fetch() Anda:
        // JS Anda memakai invoice_no / transaction_id / total.
        return response()->json([
            'ok' => true,
            'message' => 'Checkout berhasil',
            'transaction_id' => $result->id,
            'invoice_no' => $result->invoice_no,
            'status' => $result->status,
            'subtotal' => $result->subtotal,
            'tax' => $result->tax,
            'total' => $result->total,
            'snap_token' => $result->snap_token,
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
