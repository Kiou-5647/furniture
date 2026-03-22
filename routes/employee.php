<?php

use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\Setting\LookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:employee'])->prefix('nhan-vien')->group(function () {
    /**
     * Dashboard route
     */
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');

    /**
     * Lookups routes
     */
    Route::prefix('tra-cuu')->name('employee.lookups.')->group(function () {

        // View Group
        Route::middleware(['can:lookups.view'])->group(function () {
            Route::get('/{namespace?}', [LookupController::class, 'index'])->name('index');
        });

        // Manage Group
        Route::middleware(['can:lookups.manage'])->group(function () {
            Route::post('/', [LookupController::class, 'store'])->name('store');
            Route::put('/{lookup}', [LookupController::class, 'update'])->name('update');
            Route::delete('/{lookup}', [LookupController::class, 'destroy'])->name('destroy');

            // Soft Delete / Trash Sub-group
            Route::prefix('trash')->name('trash.')->group(function () {
                Route::get('/', [LookupController::class, 'trash'])->name('index');
                Route::post('/{id}/restore', [LookupController::class, 'restore'])->name('restore');
                Route::delete('/{id}/force', [LookupController::class, 'forceDestroy'])->name('force-destroy');
            });
        });
    });

});
