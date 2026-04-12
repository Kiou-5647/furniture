<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Hr\Employee;

class EmployeePolicy
{
    public function view(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('employees.view');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('employees.manage');
    }
}
