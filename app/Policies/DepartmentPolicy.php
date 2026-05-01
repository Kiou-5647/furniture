<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Hr\Department;

class DepartmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý phòng ban');
    }

    public function manage(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('Quản lý phòng ban');
    }
}
