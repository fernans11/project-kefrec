<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        if (! $midtrans->verifySignature($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $transaction = Transaction::query()
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $updates = [
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $transaction->midtrans_transaction_id,
            'payment_status' => $transactionStatus,
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

        return response()->json(['message' => 'OK']);
    }
}
