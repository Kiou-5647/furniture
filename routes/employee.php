<?php

use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\Product\CategoryController;
use App\Http\Controllers\Employee\Setting\LookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:employee'])->prefix('nhan-vien')->name('employee.')->group(function () {
    /**
     * Dashboard route
     */
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('tuy-chinh')->name('settings.')->group(function () {
        /**
         * Lookups routes
         */
        Route::prefix('tra-cuu')->name('lookups.')->group(function () {

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
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [LookupController::class, 'trash'])->name('index');
                    Route::post('/{id}/restore', [LookupController::class, 'restore'])->name('restore');
                    Route::delete('/{id}/force', [LookupController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });
    });

    /**
     * Products routes
     */
    // Route::prefix('san-pham')->name('products.')->group(function () {
    //    // Categories routes
    //    Route::prefix('danh-muc')->name('categories.')->group(function () {
    //        // View Group
    //        Route::middleware(['can:categories.view'])->group(function () {
    //            Route::get('/{groupSlug?}', [CategoryController::class, 'index'])->name('index');
    //        });

    //        // Manage Group
    //        Route::middleware(['can:categories.manage'])->group(function () {
    //            Route::post('/', [CategoryController::class, 'store'])->name('store');
    //            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    //            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

    //            // Soft Delete / Trash Sub-group
    //            Route::prefix('thung-rac')->name('trash.')->group(function () {
    //                Route::get('/', [CategoryController::class, 'trash'])->name('index');
    //                Route::post('/{id}/restore', [CategoryController::class, 'restore'])->name('restore');
    //                Route::delete('/{id}/force', [CategoryController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
    //            });
    //        });
    //    });
    // });
});
