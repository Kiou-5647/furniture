<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Discount;

class DiscountPolicy
{
    public function viewDiscount(User $user)
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('discounts.view');
    }

    public function createDiscount(User $user)
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('discounts.manage');
    }

    public function manageDiscount(User $user, Discount $discount)
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('discounts.manage');
    }
}
