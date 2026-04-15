<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Product\Bundle;

class BundlePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bundles.create');
    }

    public function manage(User $user, Bundle $bundle): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bundles.manage');
    }
}
