<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Product\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PRODUCT['SELECT']);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(Permission::PRODUCT['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PRODUCT['CREATE']);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(Permission::PRODUCT['UPDATE']);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(Permission::PRODUCT['DELETE']);
    }
}
