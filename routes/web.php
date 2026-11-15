<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Customer\MemberController;
use App\Http\Controllers\Cashier\OrderApprovalController;
use App\Http\Controllers\Kitchen\OrderBoardController;
use App\Http\Controllers\Kitchen\IngredientStockController;
use App\Http\Controllers\Attendance\AttendanceSelfController;
use App\Http\Controllers\MidtransNotificationController;

/*
|--------------------------------------------------------------------------
| Public (Guest Customer)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'landing'])->name('landing');

// Opsional: tetap buka landing yang sama (SPA-like via JS)
Route::get('/menu', [HomeController::class, 'landing'])->name('menu.page');
Route::get('/cart', [HomeController::class, 'landing'])->name('cart.page');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle'])->name('midtrans.notification');

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
    Route::get('/member', [MemberController::class, 'index'])->name('member.show');
    Route::get('/member/orders/active', [MemberController::class, 'activeOrders'])->name('member.orders.active');
    Route::post('/member/complaints', [MemberController::class, 'storeComplaint'])->name('member.complaints.store');

    // Halaman absensi khusus karyawan kasir/dapur
    Route::get('/attendance', [AttendanceSelfController::class, 'employee'])->name('attendance.self');
    Route::get('/employee/attendance', [AttendanceSelfController::class, 'employee'])->name('employee.attendance');
    Route::post('/attendance/check-in', [AttendanceSelfController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceSelfController::class, 'checkOut'])->name('attendance.check-out');
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

    // orders biarkan dulu seperti punya Anda (tidak kita fokuskan)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{transaction}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{transaction}/sync-payment', [OrderController::class, 'syncPayment'])->name('orders.sync-payment');
    Route::post('/orders/{transaction}/cancel-payment', [OrderController::class, 'cancelPayment'])->name('orders.cancel-payment');

    // Kasir: persetujuan pesanan
    Route::get('/cashier/orders', [OrderApprovalController::class, 'index'])->name('cashier.orders.index');
    Route::post('/cashier/orders/{transaction}/approve', [OrderApprovalController::class, 'approve'])->name('cashier.orders.approve');

    // Dapur: board pesanan
    Route::get('/kitchen/orders', [OrderBoardController::class, 'index'])->name('kitchen.orders.index');
    Route::post('/kitchen/orders/{transaction}/ready', [OrderBoardController::class, 'markReady'])->name('kitchen.orders.ready');
    Route::post('/kitchen/orders/{transaction}/completed', [OrderBoardController::class, 'markCompleted'])->name('kitchen.orders.completed');

    // Dapur: cek stok bahan baku
    Route::get('/kitchen/ingredients', [IngredientStockController::class, 'index'])->name('kitchen.ingredients.index');
});
