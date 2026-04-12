<?php

namespace App\Actions\Hr;

use App\Models\Auth\Permission;
use App\Models\Auth\User;

class GrantEmployeePermissionAction
{
    public function execute(User $user, string $permissionName): User
    {
        $permission = Permission::findByName($permissionName);
        $user->givePermissionTo($permission);

        return $user->load('permissions');
    }
}
