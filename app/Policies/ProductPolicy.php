<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Product\Product;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('products.create');
    }

    public function manage(User $user, Product $product): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('products.manage');
    }
}
