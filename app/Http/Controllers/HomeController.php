<?php

namespace App\Http\Controllers;

use App\Support\MemberStats;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function landing()
    {
        if (Auth::check()) {
            $staffRoute = $this->staffRoute(Auth::user()->usertype);

            if ($staffRoute) {
                return redirect()->route($staffRoute);
            }
        }

        return view('customer.landing', [
            'memberStats' => Auth::check()
                ? MemberStats::forCustomer(Auth::user()->customer)
                : MemberStats::empty(),
        ]);
    }

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

        $staffRoute = $this->staffRoute($usertype);
        if ($staffRoute) {
            return redirect()->route($staffRoute);
        }

        // selain admin -> halaman member
        return redirect()->route('home');
    }

    public function userDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $staffRoute = $this->staffRoute(Auth::user()->usertype);
        if ($staffRoute) {
            return redirect()->route($staffRoute);
        }

        // Halaman member tetap pakai landing yang sama, UI berubah via @auth/@guest
        return view('customer.landing', [
            'memberStats' => MemberStats::forCustomer(Auth::user()->customer),
        ]);
    }

    private function staffRoute(?string $usertype): ?string
    {
        if (in_array($usertype, ['cashier', 'kasir'], true)) {
            return 'cashier.orders.index';
        }

        if (in_array($usertype, ['kitchen', 'dapur'], true)) {
            return 'kitchen.orders.index';
        }

        return null;
    }
}
