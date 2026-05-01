<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Product\Category;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý danh mục');
    }

    public function manage(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('Quản lý danh mục');
    }
}
