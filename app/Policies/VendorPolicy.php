<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Vendor\Vendor;

class VendorPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý nhà cung cấp');
    }

    public function manage(User $user, Vendor $vendor): bool
    {
        return $user->hasPermissionTo('Quản lý nhà cung cấp');
    }
}
