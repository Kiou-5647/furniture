<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Order;

class OrderPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.create');
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.update_status');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.cancel');
    }

    public function complete(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.complete');
    }

    public function markAsPaid(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.mark_paid');
    }

    public function manage(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.manage');
    }

    public function createShipments(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.create');
    }

    public function storeShipments(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.create');
    }
}
