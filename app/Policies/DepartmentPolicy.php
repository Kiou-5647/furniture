<?php

namespace App\Policies;

use App\Models\Auth\User;

class DepartmentPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('hr.departments.view');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('hr.departments.manage');
    }
}
