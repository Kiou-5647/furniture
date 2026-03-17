<?php

namespace App\Services\Employee;

use App\Models\Auth\User;

class EmployeeDashboardService
{
    /**
     * Get employee dashboard data.
     *
     * @return array{name: string, email: string, roles: array, permissions: array, employee: mixed}
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
            'employee' => $user->employee,
        ];
    }
}
