<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Vendor\Vendor;

class VendorPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('vendors.create');
    }

    public function manage(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('vendors.manage');
    }
}
