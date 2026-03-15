<?php

use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorPendingVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:vendor'])->group(function () {
    Route::get('/nha-cung-cap/cho-xac-minh', [VendorPendingVerificationController::class, 'show'])
        ->name('vendor.pending-verification');
});

Route::middleware(['auth', 'verified', 'user_type:vendor', 'vendor_verified'])->group(function () {
    Route::get('/nha-cung-cap/dashboard', [VendorDashboardController::class, 'index'])
        ->name('vendor.dashboard');
});
