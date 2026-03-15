<?php

use App\Http\Controllers\Vendor\VendorDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:vendor'])->group(function () {
    Route::get('/nha-cung-cap/dashboard', [VendorDashboardController::class, 'index'])
        ->name('vendor.dashboard');
});
