<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Booking\DesignService;

class DesignServicePolicy
{
    public function manage(User $user, DesignService $service): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('design_services.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('design_services.manage');
    }
}
