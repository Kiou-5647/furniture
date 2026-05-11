<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Sales\Order;

class OrderPolicy
{
    private function canAccess(User $user, Order $order): bool
    {
        // 1. Check quyền quản lý
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        // 2. Check vai trò nhân viên
        if (!$user->isEmployee()) {
            return false;
        }

        $employee = $user->employee;

        // 3. Check đơn hàng có thuộc cửa hàng của nhân viên không hoặc đơn online.
        if (!is_null($order->store_location_id) && $order->store_location_id !== $employee?->store_location_id) {
            return false;
        }

        // 4. Check quyền quản lý trong chi nhánh
        if ($user->hasRole('Quản lý cửa hàng')) {
            return true;
        }

        // 5. Check xem liệu nhân viên có phải là người đảm nhận đơn hàng đó không.
        return is_null($order->accepted_by) || $order->accepted_by === $employee?->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ORDER['SELECT']);
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isEmployee() && $user->hasPermissionTo(Permission::ORDER['SELECT'])) {
            return $this->canAccess($user, $order);
        }

        if ($user->isCustomer()) {
            return $order->customer_id === $user->customer?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ORDER['CREATE']);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(Permission::ORDER['UPDATE'])
            && $this->canAccess($user, $order);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->updateStatus($user, $order);
    }

    public function complete(User $user, Order $order): bool
    {
        return $this->updateStatus($user, $order);
    }

    public function markAsPaid(User $user, Order $order): bool
    {
        return $this->updateStatus($user, $order);
    }

    public function createShipments(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(Permission::SHIPMENT['CREATE'])
            && $this->canAccess($user, $order);
    }

    public function storeShipments(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(Permission::SHIPMENT['CREATE'])
            && $this->canAccess($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(Permission::ORDER['DELETE'])
            && $this->canAccess($user, $order);
    }
}
