<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Product\Collection;

class CollectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::COLLECTION['SELECT']);
    }

    public function view(User $user, Collection $collection): bool
    {
        return $user->hasPermissionTo(Permission::COLLECTION['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::COLLECTION['CREATE']);
    }

    public function update(User $user, Collection $collection): bool
    {
        return $user->hasPermissionTo(Permission::COLLECTION['UPDATE']);
    }

    public function delete(User $user, Collection $collection): bool
    {
        return $user->hasPermissionTo(Permission::COLLECTION['DELETE']);
    }
}
