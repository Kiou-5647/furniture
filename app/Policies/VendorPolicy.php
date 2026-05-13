<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Vendor\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::VENDOR['SELECT']);
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->hasPermissionTo(Permission::VENDOR['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::VENDOR['CREATE']);
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->hasPermissionTo(Permission::VENDOR['UPDATE']);
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->hasPermissionTo(Permission::VENDOR['DELETE']);
    }
}
