<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\OrderController;

/*
|--------------------------------------------------------------------------
| Public (Guest Customer)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('customer.landing'))->name('landing');

// Opsional: tetap buka landing yang sama (SPA-like via JS)
Route::get('/menu', fn () => view('customer.landing'))->name('menu.page');
Route::get('/cart', fn () => view('customer.landing'))->name('cart.page');

/*
|--------------------------------------------------------------------------
| Authenticated (Jetstream/Fortify)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Default Fortify/Jetstream redirect after login biasanya /dashboard
    // lalu kita putuskan redirect berdasarkan usertype
    Route::get('/dashboard', [HomeController::class, 'redirectAfterLogin'])->name('dashboard');

    // Halaman member/customer setelah login
    Route::get('/home', [HomeController::class, 'userDashboard'])->name('home');
});

/*
|--------------------------------------------------------------------------
| Cart Actions (POST)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->group(function () {
    Route::post('/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');
});

/*
|--------------------------------------------------------------------------
| Checkout & Orders (Require Login)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // orders biarkan dulu seperti punya Anda (tidak kita fokuskan)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{transaction}', [OrderController::class, 'show'])->name('orders.show');
});
