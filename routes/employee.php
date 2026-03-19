<?php

use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\LookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:employee'])->group(function () {
    /**
     * Dashboard route
     */
    Route::get('/nhan-vien/dashboard', [EmployeeDashboardController::class, 'index'])
        ->name('employee.dashboard');

    /**
     * Lookups routes
     */
    Route::middleware(['can:lookup.view'])->get('/nhan-vien/tra-cuu', [LookupController::class, 'index'])
        ->name('employee.lookups.index');

    Route::middleware(['can:lookup.view'])->get('/nhan-vien/tra-cuu/{lookup:key}', [LookupController::class, 'show'])
        ->name('employee.lookups.show');

    Route::middleware(['can:lookup.create'])->post('/nhan-vien/tra-cuu', [LookupController::class, 'store'])
        ->name('employee.lookups.store');

    Route::middleware(['can:lookup.update'])->put('/nhan-vien/tra-cuu/{lookup}', [LookupController::class, 'update'])
        ->name('employee.lookups.update');

    Route::middleware(['can:lookup.delete'])->delete('/nhan-vien/tra-cuu/{lookup}', [LookupController::class, 'destroy'])
        ->name('employee.lookups.destroy');
});
