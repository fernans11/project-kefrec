<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $customer = $user?->customer;

        $orders = Transaction::query()
            ->when($customer, fn ($q) => $q->where('customer_id', $customer->id))
            ->latest()
            ->paginate(10);

        return view('shop.orders', compact('orders'));
    }

    public function show(Transaction $transaction)
    {
        // Simple security: pastikan hanya lihat order miliknya (berdasarkan email customer)
        $user = auth()->user();
        $customer = $user?->customer;

        abort_if(!$customer || $transaction->customer_id !== $customer->id, 403);

        $transaction->load(['customer', 'items.product']);

        return view('shop.order-show', compact('transaction'));
    }

    public function syncPayment(Transaction $transaction, MidtransService $midtrans): JsonResponse
    {
        $this->authorizeCustomerOrder($transaction);

        if (! $transaction->midtrans_order_id) {
            return response()->json([
                'ok' => false,
                'message' => 'Transaksi ini tidak memakai pembayaran Midtrans.',
            ], 422);
        }

        try {
            $payload = $midtrans->getStatus($transaction->midtrans_order_id);
            $this->applyMidtransStatus($transaction, $payload);
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengecek status Midtrans: ' . $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'status' => $transaction->fresh()->status,
            'payment_status' => $transaction->fresh()->payment_status,
        ]);
    }

    public function cancelPayment(Transaction $transaction, MidtransService $midtrans): JsonResponse|RedirectResponse
    {
        $this->authorizeCustomerOrder($transaction);

        if ($transaction->status !== 'pending_payment') {
            $message = 'Pesanan tidak bisa dibatalkan dari status saat ini.';

            if (request()->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        if ($transaction->midtrans_order_id) {
            try {
                $midtrans->cancel($transaction->midtrans_order_id);
            } catch (\Throwable) {
                // Local order tetap dibatalkan agar customer tidak tersangkut di pending.
            }
        }

        $transaction->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'status' => 'cancelled']);
        }

        return redirect()->route('orders.show', $transaction)->with('success', 'Pesanan dibatalkan.');
    }

    private function authorizeCustomerOrder(Transaction $transaction): void
    {
        $customer = auth()->user()?->customer;

        abort_if(!$customer || $transaction->customer_id !== $customer->id, 403);
    }

    private function applyMidtransStatus(Transaction $transaction, array $payload): void
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $updates = [
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $transaction->midtrans_transaction_id,
            'payment_status' => $transactionStatus ?: $transaction->payment_status,
            'payment_type' => $payload['payment_type'] ?? $transaction->payment_type,
            'payment_payload' => $payload,
        ];

        if (
            in_array($transactionStatus, ['settlement', 'capture'], true)
            && ($transactionStatus !== 'capture' || $fraudStatus === 'accept')
        ) {
            $updates['paid_at'] = $transaction->paid_at ?? now();
            $updates['paid_amount'] = $transaction->total;
            $updates['change_amount'] = 0;

            if ($transaction->status === 'pending_payment') {
                $updates['status'] = 'pending_cashier';
            }
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            if ($transaction->status === 'pending_payment') {
                $updates['status'] = 'cancelled';
            }
        }

        $transaction->update($updates);
    }
}
