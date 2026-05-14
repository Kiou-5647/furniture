<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Hr\Employee;

class EmployeePolicy
{
    private function canAccess(User $user, Employee $employee): bool
    {
        // 1. Check quyền quản lý cấp cao
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        // 2. Check vai trò nhân viên
        if (! $user->isEmployee()) {
            return false;
        }

        $me = $user->employee;

        // 3. Quản lý kho hàng: Xem NV thuộc kho mình quản lý
        if ($user->hasRole('Quản lý kho hàng')) {
            return $employee->warehouse_location_id &&
                $employee->warehouseLocation?->manager_id === $me->id; // So sánh Employee ID
        }

        // 4. Quản lý cửa hàng: Xem NV thuộc cửa hàng mình quản lý
        if ($user->hasRole('Quản lý cửa hàng')) {
            return $employee->store_location_id &&
                $employee->storeLocation?->manager_id === $me->id; // So sánh Employee ID
        }

        // 5. Nhân viên bình thường: Chỉ xem được chính mình
        return $employee->id === $me->id || $user->hasPermissionTo(Permission::EMPLOYEE['SELECT']);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EMPLOYEE['SELECT']);
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->isEmployee() && $user->hasPermissionTo(Permission::EMPLOYEE['SELECT'])) {
            return $this->canAccess($user, $employee);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EMPLOYEE['CREATE']);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo(Permission::EMPLOYEE['UPDATE'])
            && $this->canAccess($user, $employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo(Permission::EMPLOYEE['DELETE'])
            && $this->canAccess($user, $employee);
    }

    public function grant(User $user, Employee $employee): bool
    {
        return $this->canAccess($user, $employee) &&
            $user->hasPermissionTo(Permission::PERMISSION['GRANT']);
    }
}
