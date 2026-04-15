<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Product\Category;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('categories.create');
    }

    public function manage(User $user, Category $category): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('categories.manage');
    }
}
