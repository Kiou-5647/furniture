<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Setting\Lookup;

class LookupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(\App\Constants\Permission::LOOKUP['SELECT']);
    }

    public function view(User $user, Lookup $lookup): bool
    {
        return $user->hasPermissionTo(\App\Constants\Permission::LOOKUP['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(\App\Constants\Permission::LOOKUP['CREATE']);
    }

    public function update(User $user, Lookup $lookup): bool
    {
        return $user->hasPermissionTo(\App\Constants\Permission::LOOKUP['UPDATE']);
    }

    public function delete(User $user, Lookup $lookup): bool
    {
        return $user->hasPermissionTo(\App\Constants\Permission::LOOKUP['DELETE']);
    }
}
