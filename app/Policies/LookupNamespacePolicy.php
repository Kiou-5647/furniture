<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Setting\LookupNamespace;

class LookupNamespacePolicy
{
    public function manage(User $user, LookupNamespace $lookupNamespace): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('lookups.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('lookups.manage');
    }
}
