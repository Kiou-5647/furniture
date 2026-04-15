<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Inventory\StockTransfer;

class StockTransferPolicy
{
    public function manage(User $user, StockTransfer $transfer): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('inventory.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('inventory.manage');
    }

    public function ship(User $user, StockTransfer $transfer): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('inventory.manage');
    }

    public function receive(User $user, StockTransfer $transfer): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('inventory.manage');
    }

    public function cancel(User $user, StockTransfer $transfer): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('inventory.manage');
    }
}
