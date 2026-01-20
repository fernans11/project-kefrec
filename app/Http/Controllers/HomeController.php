<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function redirectAfterLogin()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usertype = Auth::user()->usertype;

        // admin/owner -> panel Filament
        if (in_array($usertype, ['admin', 'owner'], true)) {
            return redirect('/admin');
        }

        // kasir -> halaman persetujuan pesanan
        if (in_array($usertype, ['cashier', 'kasir'], true)) {
            return redirect()->route('cashier.orders.index');
        }

        // dapur -> board pesanan
        if (in_array($usertype, ['kitchen', 'dapur'], true)) {
            return redirect()->route('kitchen.orders.index');
        }

        // selain admin -> halaman member
        return redirect()->route('home');
    }

    public function userDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Halaman member tetap pakai landing yang sama, UI berubah via @auth/@guest
        return view('customer.landing');
    }
}
