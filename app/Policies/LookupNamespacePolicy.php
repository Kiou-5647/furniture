<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Setting\LookupNamespace;

class LookupNamespacePolicy
{
    public function manage(User $user, LookupNamespace $lookupNamespace): bool
    {
        return $user->hasPermissionTo('Quản lý tra cứu');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý tra cứu');
    }
}
