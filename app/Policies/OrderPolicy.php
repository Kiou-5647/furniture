<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Order;

class OrderPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function complete(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function markAsPaid(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function manage(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function createShipments(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }

    public function storeShipments(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('Quản lý đơn hàng');
    }
}
