<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

// Halaman dashboard user biasa (tanpa redirect ke admin)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // setelah login Jetstream akan ke /dashboard
    Route::get('/dashboard', [HomeController::class, 'redirectAfterLogin'])
        ->name('dashboard');

    // ini halaman khusus user, tampilan Jetstream
    Route::get('/home', [HomeController::class, 'userDashboard'])
        ->name('home');
});
