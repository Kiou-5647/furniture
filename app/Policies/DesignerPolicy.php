<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Hr\Designer;

class DesignerPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('designers.create');
    }

    public function manage(User $user, Designer $designer): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('designers.manage');
    }
}
