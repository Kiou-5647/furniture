<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Inventory\StockTransfer;

class StockTransferPolicy
{
    public function manage(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }

    public function ship(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }

    public function receive(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }

    public function cancel(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermissionTo('Quản lý kho hàng');
    }
}
