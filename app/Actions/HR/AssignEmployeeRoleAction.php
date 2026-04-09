<?php

namespace App\Actions\HR;

use App\Models\Auth\User;
use Spatie\Permission\Models\Role;

class AssignEmployeeRoleAction
{
    public function execute(User $user, string $roleName): User
    {
        $role = Role::findByName($roleName);
        $user->assignRole($role);

        return $user->load('roles');
    }
}
