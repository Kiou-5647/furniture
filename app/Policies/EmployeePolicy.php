<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Employee\Employee;

class EmployeePolicy
{
    public function view(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('hr.employees.view');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('hr.employees.manage');
    }
}
