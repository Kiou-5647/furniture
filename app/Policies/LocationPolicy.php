<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Inventory\Location;

class LocationPolicy
{
    public function manage(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }
}
