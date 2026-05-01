<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Discount;

class DiscountPolicy
{
    public function viewDiscount(User $user)
    {
        return $user->hasPermissionTo('Xem khuyến mãi');
    }

    public function createDiscount(User $user)
    {
        return $user->hasPermissionTo('Quản lý khuyến mãi');
    }

    public function manageDiscount(User $user, Discount $discount)
    {
        return $user->hasPermissionTo('Quản lý khuyến mãi');
    }
}
