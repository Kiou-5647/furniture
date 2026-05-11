<?php

use App\Http\Controllers\Api\BookingAvailabilityController;
use App\Http\Controllers\Api\GeodataController;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

/**
 * Google Auth
 */
Route::name('auth.google.')->group(function () {
    Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('redirect');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('callback');
});

/**
 * Dữ liệu tỉnh thành
 */
Route::get('/geodata/provinces', [GeodataController::class, 'provinces']);
Route::get('/geodata/wards', [GeodataController::class, 'wards']);

/**
 * Lịch làm việc và lịch trống của nhà thiết kế
 */
Route::name('designers.')->group(function () {
    Route::get('lich-lam-viec/{designer}/availabilities', [BookingAvailabilityController::class, 'availabilities'])->name('availabilities');
    Route::get('lich-lam-viec/{designer}/available-slots', [BookingAvailabilityController::class, 'availableSlots'])->name('available-slots');
});
