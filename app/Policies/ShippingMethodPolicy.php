<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Fulfillment\ShippingMethod;

class ShippingMethodPolicy
{
    public function manage(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipping_methods.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipping_methods.manage');
    }
}
