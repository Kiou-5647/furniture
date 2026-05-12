<?php

use App\Http\Controllers\Employee\Booking\BookingController;
use App\Http\Controllers\Employee\Customer\CustomerController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\Fulfillment\ShipmentController;
use App\Http\Controllers\Employee\Fulfillment\ShippingMethodController;
use App\Http\Controllers\Employee\Hr\DepartmentController;
use App\Http\Controllers\Employee\Hr\DesignerController;
use App\Http\Controllers\Employee\Hr\EmployeeController;
use App\Http\Controllers\Employee\Inventory\LocationController;
use App\Http\Controllers\Employee\Inventory\StockAdjustmentController;
use App\Http\Controllers\Employee\Inventory\StockMovementController;
use App\Http\Controllers\Employee\Inventory\StockTransferController;
use App\Http\Controllers\Employee\Inventory\VariantDetailsController;
use App\Http\Controllers\Employee\Product\BundleController;
use App\Http\Controllers\Employee\Product\CategoryController;
use App\Http\Controllers\Employee\Product\CollectionController;
use App\Http\Controllers\Employee\Product\ProductController;
use App\Http\Controllers\Employee\Sales\DiscountController;
use App\Http\Controllers\Employee\Sales\InvoiceController;
use App\Http\Controllers\Employee\Sales\OrderController;
use App\Http\Controllers\Employee\Sales\PaymentController;
use App\Http\Controllers\Employee\Sales\RefundController;
use App\Http\Controllers\Employee\Setting\GeneralSettingsController;
use App\Http\Controllers\Employee\Setting\LookupController;
use App\Http\Controllers\Employee\Setting\LookupNamespaceController;
use App\Http\Controllers\Employee\Vendor\VendorController;
use App\Http\Controllers\Setting\EmployeeProfileController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'user_type:employee'])->prefix('nhan-vien')->name('employee.')->group(function () {
    Route::get('/ho-so', [EmployeeProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/ho-so', [EmployeeProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified', 'user_type:employee'])->prefix('nhan-vien')->name('employee.')->group(function () {
    /**
     * Dashboard
     */
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/summary', [EmployeeDashboardController::class, 'getSummary'])->name('dashboard.summary');
    Route::get('/dashboard/orders-trend', [EmployeeDashboardController::class, 'getOrdersTrend'])->name('dashboard.orders-trend');
    Route::get('/dashboard/financial-analysis', [EmployeeDashboardController::class, 'getFinancialAnalysis'])->name('dashboard.financial-analysis');

    /**
     * Routes nhân sự
     */
    Route::prefix('quan-ly-nhan-su')->name('hr.')->group(function () {

        /**
         * Routes nhân viên
         */
        Route::prefix('nhan-vien')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');

            Route::post('/', [EmployeeController::class, 'store'])->name('store');
            Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
            Route::post('/{employee}/terminate', [EmployeeController::class, 'terminate'])->name('terminate');
            Route::post('/{employee}/restore', [EmployeeController::class, 'restore'])->name('restore');

            Route::get('/{employee}/permissions', [EmployeeController::class, 'permissions'])->name('permissions');
            Route::post('/{employee}/sync-roles-permissions', [EmployeeController::class, 'syncRolesPermissions'])->name('sync-roles-permissions');
        });

        /**
         * Routes phòng ban
         */
        Route::prefix('phong-ban')->name('departments.')->group(function () {
            Route::get('/', [DepartmentController::class, 'index'])->name('index');
            Route::post('/', [DepartmentController::class, 'store'])->name('store');
            Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
            Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('nha-thiet-ke')->name('designers.')->group(function () {
            Route::get('/', [DesignerController::class, 'index'])->name('index');
            Route::post('/', [DesignerController::class, 'store'])->name('store');
            Route::put('/{designer}', [DesignerController::class, 'update'])->name('update');
            Route::delete('/{designer}', [DesignerController::class, 'destroy'])->name('destroy');
            Route::post('/{designer}/restore', [DesignerController::class, 'restore'])->name('restore')->withTrashed();
        });
    });

    /**
     * Routes đặt lịch
     */
    Route::prefix('dat-lich')->name('booking.')->group(function () {

        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');

        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::post('/{booking}/confirm', [BookingController::class, 'confirm'])->name('confirm');
        Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');

        Route::post('/{booking}/open-invoice', [BookingController::class, 'openInvoice'])->name('open-invoice');
        Route::post('/bookings/{booking}/mark-paid', [BookingController::class, 'markAsPaid'])->name('bookings.mark-paid');

        Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy');
    });

    /**
     * Routes bán hàng
     */
    Route::prefix('ban-hang')->name('sales.')->group(function () {
        Route::prefix('giam-gia')->name('discounts.')->group(function () {
            // Routes lấy data
            Route::get('targets/variants', [DiscountController::class, 'getVariants'])->name('targets.variants');
            Route::get('targets/products', [DiscountController::class, 'getProducts'])->name('targets.products');
            Route::get('targets/categories', [DiscountController::class, 'getCategories'])->name('targets.categories');
            Route::get('targets/collections', [DiscountController::class, 'getCollections'])->name('targets.collections');
            Route::get('targets/vendors', [DiscountController::class, 'getVendors'])->name('targets.vendors');

            // Routes discount
            Route::get('/', [DiscountController::class, 'index'])->name('index');
            Route::post('/', [DiscountController::class, 'store'])->name('store');
            Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
            Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
        });

        /**
         * Routes đơn hàng
         */
        // Orders
        Route::prefix('don-hang')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/export', [OrderController::class, 'export'])->name('export');
            Route::get('/catalog', [OrderController::class, 'catalog'])->name('catalog');
            Route::get('/stock-options', [OrderController::class, 'stockOptions'])->name('stock-options');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/mark-paid', [OrderController::class, 'markAsPaid'])->name('mark-paid');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/complete', [OrderController::class, 'complete'])->name('complete');
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
        });

        /**
         * Routes hoàn tiền
         */
        Route::prefix('hoan-tien')->name('refunds.')->group(function () {
            Route::get('/', [RefundController::class, 'index'])->name('index');
            Route::post('/{refund}/mark-processing', [RefundController::class, 'markProcessing'])->name('mark-processing');
            Route::post('/{refund}/approve', [RefundController::class, 'approve'])->name('approve');
            Route::post('/{refund}/reject', [RefundController::class, 'reject'])->name('reject');
            Route::get('/{refund}', [RefundController::class, 'show'])->name('show');
        });

        /**
         * Routes hóa đơn
         */
        Route::prefix('hoa-don')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        });

        /**
         * Routes thanh toán
         */
        Route::prefix('thanh-toan')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
        });
    });

    /**
     * Routes vận chuyển
     */
    Route::prefix('van-chuyen')->name('fulfillment.')->group(function () {

        /**
         * Routes đơn vận chuyển
         */
        Route::name('shipments.')->group(function () {
            Route::get('/', [ShipmentController::class, 'index'])->name('index');
            Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('show');
            Route::get('/{order}/create-shipments', [OrderController::class, 'createShipments'])->name('create');
            Route::post('/{order}/store-shipments', [OrderController::class, 'storeShipments'])->name('store');
            Route::post('/{shipment}/ship', [ShipmentController::class, 'ship'])->name('ship');
            Route::post('/{shipment}/deliver', [ShipmentController::class, 'deliver'])->name('deliver');
            Route::post('/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('cancel');
            Route::post('/{shipment}/resend', [ShipmentController::class, 'resend'])->name('resend');
            Route::post('/{shipment}/items/{shipmentItem}/return', [ShipmentController::class, 'returnItem'])->name('return-item');
            Route::post('/{shipment}/items/{shipmentItem}/update-location', [ShipmentController::class, 'updateItemLocation'])->name('update-item-location');
            Route::delete('/{shipment}', [ShipmentController::class, 'destroy'])->name('destroy');
        });

        /**
         * Routes phương thức vận chuyển
         */
        Route::prefix('phuong-thuc')->name('shipping-methods.')->group(function () {
            Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
            Route::post('/', [ShippingMethodController::class, 'store'])->name('store');
            Route::put('/{shippingMethod}', [ShippingMethodController::class, 'update'])->name('update');
            Route::delete('/{shippingMethod}', [ShippingMethodController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Routes thông tin khách hàng
     */
    Route::prefix('khach-hang')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::post('/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('deactivate');
    });

    Route::prefix('cau-hinh')->name('settings.')->group(function () {
        /**
         * Routes cài đặt
         */
        Route::prefix('chung')->name('general.')->group(function () {
            Route::get('/', [GeneralSettingsController::class, 'index'])->name('index');
            Route::post('/', [GeneralSettingsController::class, 'update'])->name('update');
        });

        /**
         * Routes danh mục tra cứu
         */
        Route::prefix('danh-muc-tra-cuu')->name('lookupNamespaces.')->group(function () {
            Route::get('/', [LookupNamespaceController::class, 'index'])->name('index');
            Route::post('/', [LookupNamespaceController::class, 'store'])->name('store');
            Route::put('/{lookupNamespace}', [LookupNamespaceController::class, 'update'])->name('update');
            Route::delete('/{lookupNamespace}', [LookupNamespaceController::class, 'destroy'])->name('destroy');
        });

        /**
         * Routes tra cứu
         */
        Route::prefix('tra-cuu')->name('lookups.')->group(function () {
            Route::get('/{namespace?}', [LookupController::class, 'index'])->name('index');
            Route::post('/', [LookupController::class, 'store'])->name('store');
            Route::put('/{lookup}', [LookupController::class, 'update'])->name('update');
            Route::delete('/{lookup}', [LookupController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Routes sản phẩm
     */
    Route::prefix('san-pham')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('cards/search', [ProductController::class, 'searchCards'])->name('product-cards.search');
        Route::get('/chinh-sua/{product}', [ProductController::class, 'edit'])->name('edit');
        Route::get('/tao-moi', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    });

    /**
     * Routes danh mục
     */
    Route::prefix('danh-muc')->name('categories.')->group(function () {
        Route::get('/{groupSlug?}', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    /**
     * Routes bộ sưu tập
     */
    Route::prefix('bo-suu-tap')->name('collections.')->group(function () {
        Route::get('/', [CollectionController::class, 'index'])->name('index');
        Route::post('/', [CollectionController::class, 'store'])->name('store');
        Route::put('/{collection}', [CollectionController::class, 'update'])->name('update');
        Route::delete('/{collection}', [CollectionController::class, 'destroy'])->name('destroy');
    });

    /**
     * Routes gói sản phẩm
     */
    Route::prefix('goi-san-pham')->name('bundles.')->group(function () {
        Route::get('/', [BundleController::class, 'index'])->name('index');
        Route::get('/tao-moi', [BundleController::class, 'create'])->name('create');
        Route::get('/chinh-sua/{bundle}', [BundleController::class, 'edit'])->name('edit');
        Route::post('/', [BundleController::class, 'store'])->name('store');
        Route::put('/{bundle}', [BundleController::class, 'update'])->name('update');
        Route::delete('/{bundle}', [BundleController::class, 'destroy'])->name('destroy');
    });

    /**
     * Routes kho hàng
     */
    Route::prefix('kho-hang')->name('inventory.')->group(function () {
        Route::get('/variants-details/{variant}', [VariantDetailsController::class, 'show'])->name('variants-details');

        /**
         * Routes vị trí
         */
        Route::prefix('vi-tri')->name('locations.')->group(function () {
            Route::get('/', [LocationController::class, 'index'])->name('index');
            Route::post('/', [LocationController::class, 'store'])->name('store');
            Route::put('/{location}', [LocationController::class, 'update'])->name('update');
            Route::get('/{location}', [LocationController::class, 'show'])->name('show');
            Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
            Route::post('/adjust', [StockAdjustmentController::class, 'store'])->name('adjust');
            Route::post('/{location}/bulk-import', [LocationController::class, 'bulkImport'])->name('bulk-import');
            Route::get('/{location}/export', [LocationController::class, 'export'])->name('export');
        });

        /**
         * Routes chuyển kho
         */
        Route::prefix('chuyen-kho')->name('transfers.')->group(function () {
            Route::get('/', [StockTransferController::class, 'index'])->name('index');
            Route::get('/tao', [StockTransferController::class, 'create'])->name('create');
            Route::get('/{transfer}', [StockTransferController::class, 'show'])->name('show');

            Route::post('/', [StockTransferController::class, 'store'])->name('store');
            Route::post('/{transfer}/ship', [StockTransferController::class, 'ship'])->name('ship');
            Route::post('/{transfer}/receive', [StockTransferController::class, 'receive'])->name('receive');
            Route::post('/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('cancel');

            Route::get('/variants/{locationId}', [StockTransferController::class, 'variants'])->name('variants');
            Route::get('/locations/variant/{variantId}', [StockTransferController::class, 'locations'])->name('locations');
        });

        /**
         * Routes biến động tồn kho
         */
        Route::prefix('lich-su-ton-kho')->name('movements.')->group(function () {
            Route::get('/', [StockMovementController::class, 'index'])->name('index');
        });

        Route::prefix('nha-cung-cap')->name('vendor.')->group(function () {
            Route::get('/', [VendorController::class, 'index'])->name('index');
            Route::post('/', [VendorController::class, 'store'])->name('store');
            Route::put('/{vendor}', [VendorController::class, 'update'])->name('update');
            Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');
        });
    });
});
