<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;

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
}
