<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Product\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CATEGORY['SELECT']);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CATEGORY['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CATEGORY['CREATE']);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CATEGORY['UPDATE']);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CATEGORY['DELETE']);
    }
}
