<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Hr\Department;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DEPARTMENT['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DEPARTMENT['CREATE']);
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(Permission::DEPARTMENT['UPDATE']);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(Permission::DEPARTMENT['DELETE']);
    }
}
