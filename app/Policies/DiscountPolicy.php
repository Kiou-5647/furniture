<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Sales\Discount;

class DiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DISCOUNT['SELECT']);
    }

    public function view(User $user, Discount $discount): bool
    {
        return $user->hasPermissionTo(Permission::DISCOUNT['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DISCOUNT['CREATE']);
    }

    public function update(User $user, Discount $discount): bool
    {
        return $user->hasPermissionTo(Permission::DISCOUNT['UPDATE']);
    }

    public function delete(User $user, Discount $discount): bool
    {
        return $user->hasPermissionTo(Permission::DISCOUNT['DELETE']);
    }
}
