<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Product\Collection;

class CollectionPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('collections.create');
    }

    public function manage(User $user, Collection $collection): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('collections.manage');
    }
}
