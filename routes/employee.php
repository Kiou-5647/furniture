<?php

use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\Fulfillment\ShipmentController;
use App\Http\Controllers\Employee\Fulfillment\ShippingMethodController;
use App\Http\Controllers\Employee\Inventory\LocationController;
use App\Http\Controllers\Employee\Inventory\StockMovementController;
use App\Http\Controllers\Employee\Inventory\StockTransferController;
use App\Http\Controllers\Employee\Product\BundleController;
use App\Http\Controllers\Employee\Product\CategoryController;
use App\Http\Controllers\Employee\Product\CollectionController;
use App\Http\Controllers\Employee\Product\ProductController;
use App\Http\Controllers\Employee\Sales\InvoiceController;
use App\Http\Controllers\Employee\Sales\OrderController;
use App\Http\Controllers\Employee\Sales\PaymentController;
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

    /**
     * Sales routes
     */
    Route::prefix('ban-hang')->name('sales.')->group(function () {
        // Orders
        Route::prefix('don-hang')->name('orders.')->group(function () {
            Route::middleware(['can:orders.view'])->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('index');
                Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            });

            Route::middleware(['can:orders.manage'])->group(function () {
                Route::post('/', [OrderController::class, 'store'])->name('store');
                Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
                Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
                Route::post('/{order}/complete', [OrderController::class, 'complete'])->name('complete');
                Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');

                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [OrderController::class, 'trash'])->name('index');
                    Route::post('/{order}/restore', [OrderController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{order}/force', [OrderController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        // Invoices
        Route::prefix('hoa-don')->name('invoices.')->group(function () {
            Route::middleware(['can:invoices.view'])->group(function () {
                Route::get('/', [InvoiceController::class, 'index'])->name('index');
                Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            });

            Route::middleware(['can:invoices.manage'])->group(function () {
                Route::post('/', [InvoiceController::class, 'store'])->name('store');
                Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');

                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [InvoiceController::class, 'trash'])->name('index');
                    Route::post('/{invoice}/restore', [InvoiceController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{invoice}/force', [InvoiceController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        // Payments
        Route::prefix('thanh-toan')->name('payments.')->group(function () {
            Route::middleware(['can:payments.view'])->group(function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index');
                Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
            });

            Route::middleware(['can:payments.manage'])->group(function () {
                Route::post('/', [PaymentController::class, 'store'])->name('store');
                Route::post('/{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
                Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');

                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [PaymentController::class, 'trash'])->name('index');
                    Route::post('/{payment}/restore', [PaymentController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{payment}/force', [PaymentController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });
    });

    /**
     * Fulfillment routes
     */
    Route::prefix('van-chuyen')->name('fulfillment.')->group(function () {
        // Shipping Methods
        Route::prefix('phuong-thuc')->name('shipping-methods.')->group(function () {
            Route::middleware(['can:shipping_methods.view'])->group(function () {
                Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
            });

            Route::middleware(['can:shipping_methods.manage'])->group(function () {
                Route::post('/', [ShippingMethodController::class, 'store'])->name('store');
                Route::put('/{shippingMethod}', [ShippingMethodController::class, 'update'])->name('update');
                Route::delete('/{shippingMethod}', [ShippingMethodController::class, 'destroy'])->name('destroy');
            });
        });

        // Shipments
        Route::middleware(['can:shipments.view'])->group(function () {
            Route::get('/', [ShipmentController::class, 'index'])->name('shipments.index');
            Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
        });

        Route::middleware(['can:shipments.manage'])->group(function () {
            Route::post('/create', [ShipmentController::class, 'createShipments'])->name('shipments.create');
            Route::post('/{shipment}/ship', [ShipmentController::class, 'ship'])->name('shipments.ship');
            Route::post('/{shipment}/deliver', [ShipmentController::class, 'deliver'])->name('shipments.deliver');
            Route::delete('/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

            Route::prefix('thung-rac')->name('shipments.trash.')->group(function () {
                Route::get('/', [ShipmentController::class, 'trash'])->name('index');
                Route::post('/{shipment}/restore', [ShipmentController::class, 'restore'])->name('restore')->withTrashed();
                Route::delete('/{shipment}/force', [ShipmentController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
            });
        });
    });

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
