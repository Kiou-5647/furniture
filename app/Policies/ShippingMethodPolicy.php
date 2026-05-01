<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Fulfillment\ShippingMethod;

class ShippingMethodPolicy
{
    public function manage(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->hasPermissionTo('Quản lý phương thức vận chuyển');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý phương thức vận chuyển');
    }
}
