<?php

namespace App\Actions\HR;

use App\Models\Auth\User;
use Spatie\Permission\Models\Permission;

class GrantEmployeePermissionAction
{
    public function execute(User $user, string $permissionName): User
    {
        $permission = Permission::findByName($permissionName);
        $user->givePermissionTo($permission);

        return $user->load('permissions');
    }
}
