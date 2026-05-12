<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Product\Bundle;

class BundlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::BUNDLE['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::BUNDLE['CREATE']);
    }

    public function update(User $user, Bundle $bundle): bool
    {
        return $user->hasPermissionTo(Permission::BUNDLE['UPDATE']);
    }

    public function delete(User $user, Bundle $bundle): bool
    {
        return $user->hasPermissionTo(Permission::BUNDLE['DELETE']);
    }
}
