<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Support\MemberStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $customer = $user->customer;
        $stats = MemberStats::forCustomer($customer);
        $recentTransactions = $customer
            ? $customer->transactions()
                ->latest()
                ->limit(5)
                ->get(['id', 'invoice_no', 'total', 'status', 'created_at'])
            : collect();
        $complaints = $customer
            ? Complaint::query()
                ->where('customer_id', $customer->id)
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'message', 'status', 'response', 'created_at'])
            : collect();

        return view('customer.member', [
            'user' => $user,
            'customer' => $customer,
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'complaints' => $complaints,
        ]);
    }

    public function activeOrders(): View
    {
        $user = Auth::user();
        $customer = $user->customer;
        $activeOrders = $customer
            ? $customer->transactions()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest()
                ->get(['id', 'invoice_no', 'total', 'status', 'created_at'])
            : collect();

        return view('customer.active-orders', [
            'user' => $user,
            'activeOrders' => $activeOrders,
        ]);
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $user = $request->user();
        $customer = $user->customer;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'title.required' => 'Judul keluhan wajib diisi.',
            'title.max' => 'Judul keluhan maksimal 150 karakter.',
            'message.required' => 'Keluhan wajib diisi.',
            'message.min' => 'Keluhan minimal 10 karakter.',
            'message.max' => 'Keluhan maksimal 2000 karakter.',
        ]);

        Complaint::create([
            'customer_id' => $customer?->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'title' => $data['title'],
            'message' => $data['message'],
            'status' => 'open',
        ]);

        return back()->with('success', 'Keluhan berhasil dikirim dan akan ditinjau oleh admin.');
    }
}
