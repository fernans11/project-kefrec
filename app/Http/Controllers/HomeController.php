<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    // METHOD 1: dipakai setelah login (/dashboard)
    public function redirectAfterLogin()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $usertype = Auth::user()->usertype;

        // jika admin -> ke panel Filament
        if ($usertype === 'admin') {
            return redirect('/admin');
        }

        // kalau user biasa -> ke /home (method di bawah)
        return redirect()->route('home');
    }

    // METHOD 2: halaman dashboard khusus user (/home)
    public function userDashboard()
    {
        // pastikan login dulu
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('dashboard'); // view Jetstream biasa
    }
}
