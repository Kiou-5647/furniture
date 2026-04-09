<?php

namespace App\Providers;

use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Employee\Department;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\Payment;
use App\Policies\BookingPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ShipmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
    }
}
