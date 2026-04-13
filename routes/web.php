<?php

use App\Http\Controllers\Payment\VnPayReturnController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified', 'user_type:customer'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

/**
 * VNPay payment routes (public - called by VNPay gateway)
 */
Route::get('/payment/vnpay/return', VnPayReturnController::class)->name('payment.vnpay.return');
Route::inertia('/payment/vnpay/status', 'payment/vnpay-status')->name('payment.vnpay.status');

require __DIR__.'/employee.php';
require __DIR__.'/settings.php';
