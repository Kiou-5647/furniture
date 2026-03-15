<?php

namespace App\Services;

use App\Models\Auth\User;

class VendorDashboardService
{
    /**
     * Get vendor dashboard data.
     *
     * @return array{name: string, email: string, roles: array, vendor: mixed}
     */
    public function getData(User $user): array
    {
        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
                'permissions' => $user->getPermissionNames()->toArray(),
            ],
            'vendor' => $user->vendor()->first(),
        ];
    }
}
