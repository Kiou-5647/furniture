<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Hr\Department;

class DepartmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('departments.create');
    }

    public function manage(User $user, Department $department): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('departments.manage');
    }
}
