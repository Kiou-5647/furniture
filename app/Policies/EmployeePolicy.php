<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Hr\Employee;

class EmployeePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('employees.create');
    }

    public function manage(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('employees.manage');
    }

    public function terminate(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('employees.terminate');
    }

    public function syncPermissions(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('roles.manage');
    }
}
