<?php

use App\Http\Controllers\Setting\ProfileController;
use App\Http\Controllers\Setting\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('cai-dat', '/cai-dat/ho-so');

    Route::get('cai-dat/ho-so', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('cai-dat/ho-so', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('cai-dat/ho-so', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('cai-dat/bao-mat', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('cai-dat/mat-khau', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('cai-dat/giao-dien', 'settings/Appearance')->name('appearance.edit');
});
