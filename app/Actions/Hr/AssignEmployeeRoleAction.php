<?php

namespace App\Actions\Hr;

use App\Models\Auth\Role;
use App\Models\Auth\User;

class AssignEmployeeRoleAction
{
    public function execute(User $user, string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->assignRole($role);

        return $user->load('roles');
    }
}
