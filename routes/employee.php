<?php

use App\Http\Controllers\Employee\Booking\BookingController;
use App\Http\Controllers\Employee\Booking\DesignServiceController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Setting\EmployeeProfileController;
use App\Http\Controllers\Employee\Fulfillment\ShipmentController;
use App\Http\Controllers\Employee\Fulfillment\ShippingMethodController;
use App\Http\Controllers\Employee\Hr\DepartmentController;
use App\Http\Controllers\Employee\Hr\DesignerController;
use App\Http\Controllers\Employee\Hr\EmployeeController;
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
use App\Http\Controllers\Employee\Sales\RefundController;
use App\Http\Controllers\Employee\Setting\ActivityLogController;
use App\Http\Controllers\Employee\Setting\GeneralSettingsController;
use App\Http\Controllers\Employee\Setting\LookupController;
use App\Http\Controllers\Employee\Setting\LookupNamespaceController;
use App\Http\Controllers\Employee\Vendor\VendorController;
use App\Http\Controllers\Payment\VnPayPaymentController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'user_type:employee'])->prefix('nhan-vien')->name('employee.')->group(function () {
    Route::get('/ho-so', [EmployeeProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/ho-so', [EmployeeProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified', 'user_type:employee'])->prefix('nhan-vien')->name('employee.')->group(function () {
    /**
     * Dashboard route
     */
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('/nhat-ky-hoat-dong', [ActivityLogController::class, 'index'])
        ->name('activities.index');

    /**
     * HR routes
     */
    Route::prefix('quan-ly-nhan-su')->name('hr.')->group(function () {
        Route::prefix('nhan-vien')->name('employees.')->group(function () {
            Route::middleware(['can:employees.view'])->group(function () {
                Route::get('/', [EmployeeController::class, 'index'])->name('index');
                Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
            });

            Route::middleware(['can:employees.manage'])->group(function () {
                Route::post('/', [EmployeeController::class, 'store'])->name('store');
                Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
                Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
                Route::post('/{employee}/terminate', [EmployeeController::class, 'terminate'])->name('terminate');
                Route::post('/{employee}/restore', [EmployeeController::class, 'restore'])->name('restore');

                Route::middleware(['can:roles/manage'])->group(function () {
                    Route::get('/{employee}/permissions', [EmployeeController::class, 'permissions'])->name('permissions');
                    Route::post('/{employee}/sync-roles-permissions', [EmployeeController::class, 'syncRolesPermissions'])->name('sync-roles-permissions');
                });
            });
        });

        Route::prefix('phong-ban')->name('departments.')->group(function () {
            Route::middleware(['can:departments.view'])->group(function () {
                Route::get('/', [DepartmentController::class, 'index'])->name('index');
            });

            Route::middleware(['can:departments.manage'])->group(function () {
                Route::post('/', [DepartmentController::class, 'store'])->name('store');
                Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
                Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
            });
        });

        Route::prefix('nha-thiet-ke')->name('designers.')->group(function () {
            Route::middleware(['can:designers.view'])->group(function () {
                Route::get('/', [DesignerController::class, 'index'])->name('index');
                Route::get('/lich-lam-viec', [DesignerController::class, 'availabilityPage'])->name('availability');
                Route::get('/{designer}/availabilities', [DesignerController::class, 'availabilities'])->name('availabilities');
                Route::get('/{designer}/available-slots', [DesignerController::class, 'availableSlots'])->name('available-slots');
                Route::get('/{designer}/available-dates', [DesignerController::class, 'availableDates'])->name('available-dates');
            });

            Route::middleware(['can:designers.manage'])->group(function () {
                Route::post('/', [DesignerController::class, 'store'])->name('store');
                Route::put('/{designer}', [DesignerController::class, 'update'])->name('update');
                Route::delete('/{designer}', [DesignerController::class, 'destroy'])->name('destroy');
                Route::post('/{designer}/restore', [DesignerController::class, 'restore'])->name('restore')->withTrashed();
                Route::put('/{designer}/availability-slots', [DesignerController::class, 'updateAvailabilitySlots'])->name('update-availability-slots');
            });
        });
    });

    Route::prefix('dich-vu')->name('services.')->group(function () {
        Route::middleware('can:design_services.manage')->group(function () {
            Route::get('/', [DesignServiceController::class, 'index'])->name('index');
        });

        Route::middleware('can:design_services.manage')->group(function () {
            Route::post('/', [DesignServiceController::class, 'store'])->name('store');
            Route::put('/{service}', [DesignServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [DesignServiceController::class, 'destroy'])->name('destroy');
            Route::post('/{service}/restore', [DesignServiceController::class, 'restore'])->name('restore')->withTrashed();
        });
    });

    Route::prefix('dat-lich')->name('booking.')->group(function () {
        // Bookings
        Route::middleware(['can:bookings.view'])->group(function () {
            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        });

        Route::middleware(['can:bookings.manage'])->group(function () {
            Route::post('/', [BookingController::class, 'store'])->name('store');
            Route::post('/{booking}/confirm', [BookingController::class, 'confirm'])->name('confirm');
            Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
            Route::post('/{booking}/open-invoice', [BookingController::class, 'openInvoice'])->name('open-invoice');
            Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy');

            Route::prefix('thung-rac')->name('trash.')->group(function () {
                Route::get('/', [BookingController::class, 'trash'])->name('index');
                Route::post('/{booking}/restore', [BookingController::class, 'restore'])->name('restore')->withTrashed();
                Route::delete('/{booking}/force', [BookingController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
            });
        });
    });

    /**
     * Sales routes
     */
    Route::prefix('ban-hang')->name('sales.')->group(function () {
        // Orders
        Route::prefix('don-hang')->name('orders.')->group(function () {
            Route::middleware(['can:orders.view'])->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('index');
                Route::get('/catalog', [OrderController::class, 'catalog'])->name('catalog');
                Route::get('/stock-options', [OrderController::class, 'stockOptions'])->name('stock-options');
                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::get('/', [OrderController::class, 'trash'])->name('index');
                });
                Route::get('/{order}', [OrderController::class, 'show'])->name('show');
                Route::get('/{order}/create-shipments', [OrderController::class, 'createShipments'])->name('create-shipments');
                Route::post('/{order}/store-shipments', [OrderController::class, 'storeShipments'])->name('store-shipments');
            });

            Route::middleware(['can:orders.manage'])->group(function () {
                Route::post('/', [OrderController::class, 'store'])->name('store');
                Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
                Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
                Route::post('/{order}/complete', [OrderController::class, 'complete'])->name('complete');
                Route::post('/{order}/mark-paid', [OrderController::class, 'markAsPaid'])->name('mark-paid');
                Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');

                Route::prefix('thung-rac')->name('trash.')->group(function () {
                    Route::post('/{order}/restore', [OrderController::class, 'restore'])->name('restore')->withTrashed();
                    Route::delete('/{order}/force', [OrderController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
                });
            });
        });

        // Refunds
        Route::prefix('hoan-tien')->name('refunds.')->group(function () {
            Route::middleware(['can:payments.view'])->group(function () {
                Route::get('/', [RefundController::class, 'index'])->name('index');
            });
            Route::middleware(['can:payments.manage'])->group(function () {
                Route::post('/{refund}/mark-processing', [RefundController::class, 'markProcessing'])->name('mark-processing');
                Route::post('/{refund}/approve', [RefundController::class, 'approve'])->name('approve');
                Route::post('/{refund}/reject', [RefundController::class, 'reject'])->name('reject');
            });
            Route::middleware(['can:payments.view'])->group(function () {
                Route::get('/{refund}', [RefundController::class, 'show'])->name('show');
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
            });

            Route::middleware(['can:payments.manage'])->group(function () {
                Route::post('/', [PaymentController::class, 'store'])->name('store');
                Route::get('/vnpay/{invoice}', [VnPayPaymentController::class, 'initiate'])->name('vnpay.initiate');
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
                Route::post('/{shippingMethod}/restore', [ShippingMethodController::class, 'restore'])->name('restore')->withTrashed();
                Route::delete('/{shippingMethod}/force', [ShippingMethodController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
            });

            Route::prefix('thung-rac')->name('trash.')->group(function () {
                Route::get('/', [ShippingMethodController::class, 'trash'])->name('index');
            });
        });

        // Shipments
        Route::middleware(['can:shipments.view'])->group(function () {
            Route::get('/', [ShipmentController::class, 'index'])->name('shipments.index');
            Route::prefix('thung-rac')->name('shipments.trash.')->group(function () {
                Route::get('/', [ShipmentController::class, 'trash'])->name('index');
            });
            Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
        });

        Route::middleware(['can:shipments.manage'])->group(function () {
            Route::post('/create', [ShipmentController::class, 'createShipments'])->name('shipments.create');
            Route::post('/{shipment}/ship', [ShipmentController::class, 'ship'])->name('shipments.ship');
            Route::post('/{shipment}/deliver', [ShipmentController::class, 'deliver'])->name('shipments.deliver');
            Route::post('/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');
            Route::post('/{shipment}/resend', [ShipmentController::class, 'resend'])->name('shipments.resend');
            Route::post('/{shipment}/items/{shipmentItem}/return', [ShipmentController::class, 'returnItem'])->name('shipments.return-item');
            Route::post('/{shipment}/items/{shipmentItem}/update-location', [ShipmentController::class, 'updateItemLocation'])->name('shipments.update-item-location');
            Route::delete('/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

            Route::prefix('thung-rac')->name('shipments.trash.')->group(function () {
                Route::post('/{shipment}/restore', [ShipmentController::class, 'restore'])->name('restore')->withTrashed();
                Route::delete('/{shipment}/force', [ShipmentController::class, 'forceDestroy'])->name('force-destroy')->withTrashed();
            });
        });
    });

    Route::prefix('cau-hinh')->name('settings.')->group(function () {
        /**
         * General Settings routes
         */
        Route::prefix('chung')->name('general.')->group(function () {
            Route::get('/', [GeneralSettingsController::class, 'index'])->name('index');
            Route::post('/', [GeneralSettingsController::class, 'update'])->name('update');
        });

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
        Route::middleware(['can:products.view'])->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('cards/search', [ProductController::class, 'searchCards'])->name('product-cards.search');
        });

        Route::middleware(['can:products.manage'])->group(function () {
            Route::get('/chinh-sua/{product}', [ProductController::class, 'edit'])->name('edit');
            Route::get('/tao-san-pham', [ProductController::class, 'create'])->name('create');
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
        Route::middleware(['can:products.view'])->group(function () {
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        });
    });

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

    Route::prefix('goi-san-pham')->name('bundles.')->group(function () {
        Route::middleware(['can:bundles.view'])->group(function () {
            Route::get('/', [BundleController::class, 'index'])->name('index');
        });

        // Manage Group
        Route::middleware(['can:bundles.manage'])->group(function () {
            Route::get('/tao-goi-san-pham', [BundleController::class, 'create'])->name('create');
            Route::get('/chinh-sua/{bundle}', [BundleController::class, 'edit'])->name('edit');
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

        Route::prefix('nha-cung-cap')->name('vendor.')->group(function () {
            Route::get('/', [VendorController::class, 'index'])->name('index');
            Route::post('/', [VendorController::class, 'store'])->name('store');
            Route::put('/{vendor}', [VendorController::class, 'update'])->name('update');
            Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');
        });
    });
});
