<?php

namespace App\Actions\HR;

use App\Models\Auth\User;
use App\Models\Auth\Permission;

class RevokeEmployeePermissionAction
{
    public function execute(User $user, string $permissionName): User
    {
        $permission = Permission::findByName($permissionName);
        $user->revokePermissionTo($permission);

        return $user->load('permissions');
    }
}
