<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Hr\Employee;

class EmployeePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý nhân viên');
    }

    public function manage(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('Quản lý nhân viên');
    }

    public function terminate(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('Quản lý nhân viên');
    }

    public function syncPermissions(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('Quản lý nhân viên');
    }
}
