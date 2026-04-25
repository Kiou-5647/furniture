<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Payment\VnPayReturnController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, '__invoke'])->name('home');

Route::prefix('gio-hang')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::get('/data', [CartController::class, 'data'])->name('cart.data');
    Route::post('/', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/{itemId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/{itemId}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
});

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{sku}/{variant_slug}', [ProductController::class, 'show'])
    ->middleware('track.product.view')
    ->name('products.show');

Route::get('/goi-san-pham/{bundle:slug}', [App\Http\Controllers\Public\BundleController::class, 'show'])
    ->name('bundles.show');

Route::middleware(['auth', 'verified', 'user_type:customer'])->name('customer.')->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});



/**
 * VNPay payment routes (public - called by VNPay gateway)
 */
Route::get('/payment/vnpay/return', VnPayReturnController::class)->name('payment.vnpay.return');
Route::inertia('/payment/vnpay/status', 'payment/vnpay-status')->name('payment.vnpay.status');

/**
 * Google Auth
 */
Route::get('api/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('api/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

require __DIR__ . '/employee.php';
require __DIR__ . '/settings.php';
