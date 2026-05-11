<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;

class ShipmentPolicy
{
    private function canAccess(User $user, Shipment $shipment): bool
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
        $originId = $shipment->origin_location_id;

        // 3. Check vị trí xuất kho
        if ($originId !== $employee?->store_location_id && $originId !== $employee?->warehouse_location_id) {
            return false;
        }

        // 4. Check quyền quản lý chi nhánh/kho
        if ($user->hasRole('Quản lý kho hàng')) {
            return true;
        }

        // 5. Check quyền sở hữu (Người xử lý đơn vận chuyển)
        return is_null($shipment->handled_by) || $shipment->handled_by === $employee?->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SHIPMENT['SELECT']);
    }

    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isEmployee() && $user->hasPermissionTo(Permission::SHIPMENT['SELECT'])) {
            return $this->canAccess($user, $shipment);
        }

        if ($user->isCustomer()) {
            return $shipment->order->customer_id === $user->customer?->id;
        }

        return false;
    }

    public function ship(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo(Permission::SHIPMENT['UPDATE'])
            && $this->canAccess($user, $shipment);
    }

    public function deliver(User $user, Shipment $shipment): bool
    {
        return $this->ship($user, $shipment);
    }

    public function cancel(User $user, Shipment $shipment): bool
    {
        return $this->ship($user, $shipment);
    }

    public function resend(User $user, Shipment $shipment): bool
    {
        return $this->ship($user, $shipment);
    }

    public function returnItem(User $user, Shipment $shipment): bool
    {
        if ($user->hasPermissionTo(Permission::SHIPMENT['UPDATE'])) {
            if ($this->canAccess($user, $shipment)) {
                return true;
            }

            $order = $shipment->order;
            $acceptedEmployeeId = $order->accepted_by;

            if ($acceptedEmployeeId) {
                // Nếu chính là người duyệt đơn hàng
                if ($acceptedEmployeeId === $user->employee?->id) {
                    return true;
                }

                // Nếu là Quản lý cửa hàng và người duyệt đơn đó cùng chi nhánh với họ
                if ($user->hasRole('Quản lý cửa hàng')) {
                    $acceptedEmployee = \App\Models\Employee::find($acceptedEmployeeId);
                    if ($acceptedEmployee && $acceptedEmployee->store_location_id === $user->employee?->store_location_id) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo(Permission::SHIPMENT['DELETE'])
            && $this->canAccess($user, $shipment);
    }
}
