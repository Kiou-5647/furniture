<?php

namespace App\Actions\Hr;

use App\Models\Auth\Permission;
use App\Models\Auth\User;

class RevokeEmployeePermissionAction
{
    public function execute(User $user, string $permissionName): User
    {
        $permission = Permission::findByName($permissionName);
        $user->revokePermissionTo($permission);

        return $user->load('permissions');
    }
}
