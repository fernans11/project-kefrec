<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderApprovalController extends Controller
{
    public function index(): View
    {
        $this->assertCashier();

        $orders = Transaction::query()
            ->with(['customer', 'items.product'])
            ->where('status', 'pending_cashier')
            ->latest()
            ->paginate(20);

        $staffMembers = Staff::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cashier.orders', compact('orders', 'staffMembers'));
    }

    public function approve(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->assertCashier();

        if ($transaction->status !== 'pending_cashier') {
            return back()->with('error', 'Status pesanan sudah berubah.');
        }

        $transaction->update([
            'status' => 'processing',
            'cashier_id' => $transaction->cashier_id ?? $request->user()->id,
        ]);

        return back()->with('success', 'Pesanan disetujui dan diteruskan ke dapur.');
    }

    private function assertCashier(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->usertype, ['cashier', 'kasir'], true), 403);
    }
}
