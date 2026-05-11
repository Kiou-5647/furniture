<?php

namespace App\Providers;

use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Customer\Customer;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShippingMethod;
use App\Models\Hr\Department;
use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use App\Models\Product\Bundle;
use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Product\Product;
use App\Models\Sales\Discount;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\Payment;
use App\Models\Sales\Refund;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Models\Vendor\Vendor;
use App\Policies\BookingPolicy;
use App\Policies\BundlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CollectionPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DesignerPolicy;
use App\Policies\DiscountPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LocationPolicy;
use App\Policies\LookupNamespacePolicy;
use App\Policies\LookupPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RefundPolicy;
use App\Policies\ShipmentPolicy;
use App\Policies\ShippingMethodPolicy;
use App\Policies\StockTransferPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('Quản trị viên') ? true : null;
        });

        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Bundle::class, BundlePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Collection::class, CollectionPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Designer::class, DesignerPolicy::class);
        Gate::policy(Discount::class, DiscountPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(LookupNamespace::class, LookupNamespacePolicy::class);
        Gate::policy(Lookup::class, LookupPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Refund::class, RefundPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(ShippingMethod::class, ShippingMethodPolicy::class);
        Gate::policy(StockTransfer::class, StockTransferPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
    }
}
