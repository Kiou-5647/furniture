<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Order;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.view');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.update');
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('orders.force_delete');
    }
}
