<?php

use App\Http\Controllers\Payment\VnPayReturnController;
use App\Http\Controllers\Public\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, '__invoke'])->name('home');

Route::get('/san-pham/{sku}/{variant_slug}', [App\Http\Controllers\Public\ProductController::class, 'show'])
    ->middleware('track.product.view')
    ->name('public.products.show');

Route::middleware(['auth', 'verified', 'user_type:customer'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::post('/reviews', [App\Http\Controllers\Customer\ReviewController::class, 'store'])->name('customer.reviews.store');
});

/**
 * VNPay payment routes (public - called by VNPay gateway)
 */
Route::get('/payment/vnpay/return', VnPayReturnController::class)->name('payment.vnpay.return');
Route::inertia('/payment/vnpay/status', 'payment/vnpay-status')->name('payment.vnpay.status');

require __DIR__ . '/employee.php';
require __DIR__ . '/settings.php';
