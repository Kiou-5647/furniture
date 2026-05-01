<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Setting\Lookup;

class LookupPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý tra cứu');
    }

    public function manage(User $user, Lookup $lookup): bool
    {
        return $user->hasPermissionTo('Quản lý tra cứu');
    }
}
