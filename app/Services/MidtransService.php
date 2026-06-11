<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    public function createSnapToken(Transaction $transaction): string
    {
        $transaction->loadMissing(['customer.user', 'items.product']);

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->midtrans_order_id,
                'gross_amount' => (int) $transaction->total,
            ],
            'customer_details' => [
                'first_name' => $transaction->customer?->name ?? 'Customer',
                'email' => $transaction->customer?->user?->email,
            ],
            'item_details' => $transaction->items
                ->map(fn ($item) => [
                    'id' => (string) $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => (int) $item->qty,
                    'name' => (string) ($item->product?->name ?? 'Produk'),
                ])
                ->push([
                    'id' => 'tax',
                    'price' => (int) $transaction->tax,
                    'quantity' => 1,
                    'name' => 'Pajak',
                ])
                ->filter(fn ($item) => (int) Arr::get($item, 'price') > 0)
                ->values()
                ->all(),
            'callbacks' => [
                'finish' => URL::route('orders.show', $transaction),
            ],
        ];

        if ($transaction->payment_method === 'qris') {
            $params['enabled_payments'] = ['gopay', 'qris', 'shopeepay'];
        }

        if ($transaction->payment_method === 'transfer') {
            $params['enabled_payments'] = ['bca_va', 'echannel'];
        }

        return Snap::getSnapToken($params);
    }

    public function verifySignature(array $payload): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        if ($signature === '' || $orderId === '' || $statusCode === '' || $grossAmount === '') {
            return false;
        }

        $serverKey = (string) config('services.midtrans.server_key');

        return hash_equals(
            hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey),
            $signature
        );
    }

    public function getStatus(string $orderId): array
    {
        return (array) MidtransTransaction::status($orderId);
    }

    public function cancel(string $orderId): array
    {
        return (array) MidtransTransaction::cancel($orderId);
    }
}
