<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Sales\Refund;

class RefundPolicy
{
    public function canAccess(User $user, Refund $refund): bool
    {
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        if ($user->hasRole('Quản lý cửa hàng')) {
            $employee = $user->employee;
            return $refund->order?->store_location_id === $employee?->store_location_id
                || $refund->order?->storeLocation?->manager_id === $employee?->id;
        }

        if ($user->isEmployee()) {
            $employee = $user->employee;
            return $refund->requested_by === $employee?->id
                || $refund->processed_by === $employee?->id
                || $refund->order?->accepted_by === $employee?->id
                || $refund->booking?->designer_id === $employee?->designer?->id;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::REFUND['SELECT']);
    }

    public function view(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo(Permission::REFUND['SELECT']) || $this->canAccess($user, $refund);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::REFUND['CREATE']);
    }

    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo(Permission::REFUND['UPDATE']) || $this->canAccess($user, $refund);
    }

    public function reject(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo(Permission::REFUND['UPDATE']) || $this->canAccess($user, $refund);
    }

    public function delete(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo(Permission::REFUND['DELETE']) || $this->canAccess($user, $refund);
    }
}
