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
    Route::middleware(['can:lookups.view'])->group(function () {
        Route::get('/tra-cuu/{namespace?}', [LookupController::class, 'index'])->name('employee.lookups.index');
    });

    Route::middleware(['can:lookups.manage'])->prefix('tra-cuu')->group(function () {
        Route::post('/', [LookupController::class, 'store'])->name('employee.lookups.store');
        Route::put('/{lookup}', [LookupController::class, 'update'])->name('employee.lookups.update');
        Route::delete('/{lookup}', [LookupController::class, 'destroy'])->name('employee.lookups.destroy');

        Route::prefix('trash')->group(function () {
            Route::get('/', [LookupController::class, 'trash'])->name('employee.lookups.trash');
            Route::post('/{id}/restore', [LookupController::class, 'restore'])->name('employee.lookups.restore');
            Route::delete('/{id}/force', [LookupController::class, 'forceDestroy'])->name('employee.lookups.force-destroy');
        });
    });

    /**
     * Categories routes
     */
    // Route::middleware(['can:categories.view'])->group(function () {
    //    Route::get('/nhan-vien/danh-muc', [CategoryController::class, 'index'])->name('employee.categories.index');
    // });

    // Route::middleware(['can:categories.manage'])->group(function () {
    //    Route::post('/nhan-vien/danh-muc', [CategoryController::class, 'store'])->name('employee.categories.store');
    //    Route::put('/nhan-vien/danh-muc/{category}', [CategoryController::class, 'update'])->name('employee.categories.update');
    //    Route::delete('/nhan-vien/danh-muc/{category}', [CategoryController::class, 'destroy'])->name('employee.categories.destroy');
    // });

});
