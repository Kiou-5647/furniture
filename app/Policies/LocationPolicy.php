<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Inventory\Location;

class LocationPolicy
{
    private function canAccess(User $user, Location $location): bool
    {
        // 1. Quản lý luôn có quyền
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        if (!$user->isEmployee()) {
            return false;
        }

        $employee = $user->employee;

        // 2. Check khớp ID: Store Location OR Warehouse Location OR Manager ID
        return $location->id === $employee?->store_location_id
            || $location->id === $employee?->warehouse_location_id
            || $location->manager_id === $employee?->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::LOCATION['SELECT']);
    }

    public function view(User $user, Location $location): bool
    {
        return $user->hasPermissionTo(Permission::LOCATION['SELECT'])
            && $this->canAccess($user, $location);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::LOCATION['CREATE']);
    }

    public function update(User $user, Location $location): bool
    {
        return $user->hasPermissionTo(Permission::LOCATION['UPDATE'])
            && $this->canAccess($user, $location);
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->hasPermissionTo(Permission::LOCATION['DELETE'])
            && $this->canAccess($user, $location);
    }
}
