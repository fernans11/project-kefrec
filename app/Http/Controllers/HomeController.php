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

        // admin -> panel Filament
        if ($usertype === 'admin') {
            return redirect('/admin');
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
