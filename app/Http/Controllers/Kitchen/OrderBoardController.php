<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderBoardController extends Controller
{
    public function index(): View
    {
        $this->assertKitchen();

        $orders = Transaction::query()
            ->with(['items.product', 'customer'])
            ->whereIn('status', ['processing', 'ready'])
            ->latest()
            ->paginate(20);

        $staffMembers = Staff::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('kitchen.orders', compact('orders', 'staffMembers'));
    }

    public function markReady(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->assertKitchen();

        if ($transaction->status !== 'processing') {
            return back()->with('error', 'Pesanan tidak dalam status diproses.');
        }

        $transaction->update([
            'status' => 'ready',
        ]);

        return back()->with('success', 'Pesanan ditandai siap.');
    }

    public function markCompleted(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->assertKitchen();

        if ($transaction->status !== 'ready') {
            return back()->with('error', 'Pesanan belum siap diambil.');
        }

        $transaction->update([
            'status' => 'completed',
        ]);

        $transaction->deductStockIfNeeded();
        $transaction->recordCashflowIfNeeded();

        return back()->with('success', 'Pesanan selesai.');
    }

    private function assertKitchen(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->usertype, ['kitchen', 'dapur'], true), 403);
    }
}
