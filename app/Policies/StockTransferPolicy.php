<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Enums\StockTransferStatus;
use App\Models\Auth\User;
use App\Models\Inventory\StockTransfer;

class StockTransferPolicy
{
    private function canAccess(User $user, StockTransfer $transfer): bool
    {
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        if (!$user->isEmployee()) {
            return false;
        }

        $employee = $user->employee;

        return $transfer->from_location_id === $employee?->store_location_id
            || $transfer->from_location_id === $employee?->warehouse_location_id
            || $transfer->to_location_id === $employee?->store_location_id
            || $transfer->to_location_id === $employee?->warehouse_location_id
            || ($transfer->fromLocation && $transfer->fromLocation->manager_id === $employee?->id)
            || ($transfer->toLocation && $transfer->toLocation->manager_id === $employee?->id);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['SELECT']);
    }

    public function view(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['SELECT'])
            && $this->canAccess($user, $transfer);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['CREATE']);
    }

    public function update(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['UPDATE'])
            && $this->canAccess($user, $transfer);
    }

    public function ship(User $user, StockTransfer $transfer): bool
    {
        if ($user->hasRole('Quản lý')) return true;
        if (!$user->isEmployee()) return false;

        $employee = $user->employee;
        $canShip = $transfer->from_location_id === $employee?->store_location_id
            || $transfer->from_location_id === $employee?->warehouse_location_id
            || ($transfer->fromLocation && $transfer->fromLocation->manager_id === $employee?->id);

        return $user->hasPermissionTo(Permission::STOCK['UPDATE']) && $canShip;
    }

    public function receive(User $user, StockTransfer $transfer): bool
    {
        if ($user->hasRole('Quản lý')) return true;
        if (!$user->isEmployee()) return false;

        $employee = $user->employee;
        $canReceive = $transfer->to_location_id === $employee?->store_location_id
            || $transfer->to_location_id === $employee?->warehouse_location_id
            || ($transfer->toLocation && $transfer->toLocation->manager_id === $employee?->id);

        return $user->hasPermissionTo(Permission::STOCK['UPDATE']) && $canReceive;
    }

    public function cancel(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['UPDATE'])
            && $this->canAccess($user, $transfer);
    }

    public function delete(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo(Permission::STOCK['DELETE'])
            && $this->canAccess($user, $transfer);
    }
}
