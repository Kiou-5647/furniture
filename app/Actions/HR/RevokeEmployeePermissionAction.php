<?php

namespace App\Actions\HR;

use App\Models\Auth\User;
use Spatie\Permission\Models\Permission;

class RevokeEmployeePermissionAction
{
    public function execute(User $user, string $permissionName): User
    {
        $permission = Permission::findByName($permissionName);
        $user->revokePermissionTo($permission);

        return $user->load('permissions');
    }
}
