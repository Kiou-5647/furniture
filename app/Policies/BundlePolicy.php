<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Product\Bundle;

class BundlePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý gói sản phẩm');
    }

    public function manage(User $user, Bundle $bundle): bool
    {
        return $user->hasPermissionTo('Quản lý gói sản phẩm');
    }
}
