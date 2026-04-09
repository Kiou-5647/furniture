<?php

use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\Inventory\LocationController;
use App\Http\Controllers\Employee\Inventory\StockMovementController;
use App\Http\Controllers\Employee\Inventory\StockTransferController;
use App\Http\Controllers\Employee\Product\BundleController;
use App\Http\Controllers\Employee\Product\CategoryController;
use App\Http\Controllers\Employee\Product\CollectionController;
use App\Http\Controllers\Employee\Product\ProductController;
use App\Http\Controllers\Employee\Setting\ActivityLogController;
use App\Http\Controllers\Employee\Setting\LookupController;
use App\Http\Controllers\Employee\Setting\LookupNamespaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:employee'])->prefix('nhan-vien')->name('employee.')->group(function () {
    /**
     * Dashboard route
     */
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('/nhat-ky-hoat-dong', [ActivityLogController::class, 'index'])
        ->name('activities.index');

    Route::prefix('cau-hinh')->name('settings.')->group(function () {
        /**
         * Lookup Namespaces routes
         */
        Route::prefix('danh-muc-tra-cuu')->name('lookupNamespaces.')->group(function () {
            Route::middleware(['can:lookups.view'])->group(function () {
                Route::get('/', [LookupNamespaceController::class, 'index'])->name('index');
            });

            Route::middleware(['can:lookups.manage'])->group(function () {
                Route::post('/', [LookupNamespaceController::class, 'store'])->name('store');
                Route::put('/{lookupNamespace}', [LookupNamespaceController::class, 'update'])->name('update');
                Route::delete('/{lookupNamespace}', [LookupNamespaceController::class, 'destroy'])->name('destroy');
            });
        });

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
                    Route::post('/{lookup}/restore', [LookupController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{lookup}/force', [LookupController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });
    });

    /**
     * Products routes
     */
    Route::prefix('san-pham')->name('products.')->group(function () {
        // Categories routes
        Route::prefix('danh-muc')->name('categories.')->group(function () {
            // View Group
            Route::middleware(['can:categories.view'])->group(function () {
                Route::get('/{groupSlug?}', [CategoryController::class, 'index'])->name('index');
            });

            // Manage Group
            Route::middleware(['can:categories.manage'])->group(function () {
                Route::post('/', [CategoryController::class, 'store'])->name('store');
                Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
                Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

                // Soft Delete / Trash Sub-group
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [CategoryController::class, 'trash'])->name('index');
                    Route::post('/{category}/restore', [CategoryController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{category}/force', [CategoryController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        Route::prefix('bo-suu-tap')->name('collections.')->group(function () {
            // View Group
            Route::middleware(['can:collections.view'])->group(function () {
                Route::get('/', [CollectionController::class, 'index'])->name('index');
            });

            // Manage Group
            Route::middleware(['can:collections.manage'])->group(function () {
                Route::post('/', [CollectionController::class, 'store'])->name('store');
                Route::put('/{collection}', [CollectionController::class, 'update'])->name('update');
                Route::delete('/{collection}', [CollectionController::class, 'destroy'])->name('destroy');

                // Soft Delete / Trash Sub-group
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [CollectionController::class, 'trash'])->name('index');
                    Route::post('/{collection}/restore', [CollectionController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{collection}/force', [CollectionController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        Route::prefix('san-pham')->name('items.')->group(function () {
            // View Group
            Route::middleware(['can:products.view'])->group(function () {
                Route::get('/', [ProductController::class, 'index'])->name('index');
            });

            // Manage Group
            Route::middleware(['can:products.manage'])->group(function () {
                Route::post('/', [ProductController::class, 'store'])->name('store');
                Route::put('/{product}', [ProductController::class, 'update'])->name('update');
                Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

                // Soft Delete / Trash Sub-group
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [ProductController::class, 'trash'])->name('index');
                    Route::post('/{product}/restore', [ProductController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{product}/force', [ProductController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        Route::prefix('goi-san-pham')->name('bundles.')->group(function () {
            // View Group
            Route::middleware(['can:bundles.view'])->group(function () {
                Route::get('/', [BundleController::class, 'index'])->name('index');
                Route::get('/{bundle}', [BundleController::class, 'show'])->name('show');
            });

            // Manage Group
            Route::middleware(['can:bundles.manage'])->group(function () {
                Route::post('/', [BundleController::class, 'store'])->name('store');
                Route::put('/{bundle}', [BundleController::class, 'update'])->name('update');
                Route::delete('/{bundle}', [BundleController::class, 'destroy'])->name('destroy');

                // Soft Delete / Trash Sub-group
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [BundleController::class, 'trash'])->name('index');
                    Route::post('/{bundle}/restore', [BundleController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{bundle}/force', [BundleController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });
    });

    /**
     * Inventory routes
     */
    Route::prefix('kho-hang')->name('inventory.')->group(function () {
        // Locations routes
        Route::prefix('vi-tri')->name('locations.')->group(function () {
            // View Group
            Route::middleware(['can:inventory.view'])->group(function () {
                Route::get('/', [LocationController::class, 'index'])->name('index');
            });

            // Manage Group
            Route::middleware(['can:inventory.manage'])->group(function () {
                Route::post('/', [LocationController::class, 'store'])->name('store');
                Route::put('/{location}', [LocationController::class, 'update'])->name('update');
                Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');

                // Soft Delete / Trash Sub-group
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [LocationController::class, 'trash'])->name('index');
                    Route::post('/{location}/restore', [LocationController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{location}/force', [LocationController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        // Stock Transfers routes
        Route::prefix('chuyen-kho')->name('transfers.')->group(function () {
            // View Group
            Route::middleware(['can:inventory.view'])->group(function () {
                Route::get('/', [StockTransferController::class, 'index'])->name('index');
                Route::get('/tao', [StockTransferController::class, 'create'])->name('create');
                Route::get('/{transfer}', [StockTransferController::class, 'show'])->name('show');
            });

            // Manage Group
            Route::middleware(['can:inventory.manage'])->group(function () {
                Route::post('/', [StockTransferController::class, 'store'])->name('store');
                Route::post('/{transfer}/ship', [StockTransferController::class, 'ship'])->name('ship');
                Route::post('/{transfer}/receive', [StockTransferController::class, 'receive'])->name('receive');
                Route::post('/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('cancel');
            });

            // API-like routes for dynamic form data
            Route::middleware(['can:inventory.view'])->group(function () {
                Route::get('/variants/{locationId}', [StockTransferController::class, 'variants'])->name('variants');
                Route::get('/locations/variant/{variantId}', [StockTransferController::class, 'locations'])->name('locations');
            });
        });

        // Stock Movements routes
        Route::prefix('lich-su-ton-kho')->name('movements.')->group(function () {
            Route::middleware(['can:inventory.view'])->group(function () {
                Route::get('/', [StockMovementController::class, 'index'])->name('index');
            });
        });
    });
});
