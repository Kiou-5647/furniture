<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Inventory\StockMovement;

class StockMovementPolicy
{
    private function canAccess(User $user, StockMovement $movement): bool
    {
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        if (!$user->isEmployee()) {
            return false;
        }

        $employee = $user->employee;

        return $movement->location_id === $employee?->store_location_id
            || $movement->location_id === $employee?->warehouse_location_id
            || $movement->location?->manager_id === $employee?->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['SELECT']);
    }

    public function view(User $user, StockMovement $movement): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['SELECT'])
            && $this->canAccess($user, $movement);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['CREATE']);
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['UPDATE']);
    }
}
