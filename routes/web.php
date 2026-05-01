<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Payment\VnPayPaymentController;
use App\Http\Controllers\Payment\VnPayReturnController;
use App\Http\Controllers\Public\BundleController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\WelcomeController;
use App\Http\Controllers\Setting\CustomerProfileController;
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

Route::get('/goi-san-pham/{bundle:slug}', [BundleController::class, 'show'])
    ->name('bundles.show');

Route::middleware(['auth', 'verified', 'user_type:customer'])->name('customer.')->group(function () {
    Route::prefix('ho-so')->group(function () {
        Route::get('thong-tin', [CustomerProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('thong-tin', [CustomerProfileController::class, 'update'])->name('profile.update');
        Route::get('don-hang', [OrderController::class, 'index'])->name('profile.orders');
        Route::get('don-hang/{order_number}', [OrderController::class, 'show'])->name('profile.orders.show');
        Route::post('don-hang/{order_number}/cancel', [OrderController::class, 'cancel'])->name('profile.orders.cancel');
        Route::get('lich-thiet-ke', [BookingController::class, 'profileIndex'])->name('profile.bookings');
        Route::get('lich-thiet-ke/{booking_number}', [BookingController::class, 'show'])->name('profile.bookings.show');
        Route::post('lich-thiet-ke/{booking_number}/cancel', [BookingController::class, 'cancel'])->name('profile.bookings.cancel');
        Route::get('danh-gia', [ReviewController::class, 'index'])->name('profile.reviews');
    });

    Route::prefix('dat-hang')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/', [CheckoutController::class, 'store'])->name('checkout.store');
    });

    Route::prefix('dat-lich')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('/', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/designers/{designer}/availability', [BookingController::class, 'availabilities']);
        Route::get('/designers/{designer}/available-slots', [BookingController::class, 'availableSlots']);
    });

    Route::prefix('danh-gia')->group(function () {
        Route::post('/', [ReviewController::class, 'store'])->name('reviews.store');
        Route::patch('/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    });
});



/**
 * VNPay payment routes (public - called by VNPay gateway)
 */
Route::get('/vnpay/{invoice}', [VnPayPaymentController::class, 'initiate'])->name('payment.vnpay.initiate');
Route::get('/payment/vnpay/return', VnPayReturnController::class)->name('payment.vnpay.return');
Route::inertia('/payment/vnpay/status', 'payment/vnpay-status')->name('payment.vnpay.status');

/**
 * Google Auth
 */
Route::get('api/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('api/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

require __DIR__ . '/employee.php';
require __DIR__ . '/settings.php';
