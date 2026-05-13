<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Fulfillment\ShippingMethod;

class ShippingMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SHIPPING_METHOD['SELECT']);
    }

    public function view(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->hasPermissionTo(Permission::SHIPPING_METHOD['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SHIPPING_METHOD['CREATE']);
    }

    public function update(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->hasPermissionTo(Permission::SHIPPING_METHOD['UPDATE']);
    }

    public function delete(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->hasPermissionTo(Permission::SHIPPING_METHOD['DELETE']);
    }
}
