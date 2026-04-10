<?php

namespace App\Actions\HR;

use App\Models\Auth\User;
use App\Models\Auth\Permission;

class GrantEmployeePermissionAction
{
    public function execute(User $user, string $permissionName): User
    {
        $permission = Permission::findByName($permissionName);
        $user->givePermissionTo($permission);

        return $user->load('permissions');
    }
}
