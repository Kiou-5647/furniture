<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Sales\Invoice;

class InvoicePolicy
{
    private function canAccess(User $user, Invoice $invoice): bool
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

        // 3. Check xem liệu nhân viên có phải là người xác nhận hóa đơn này không
        if ($invoice->validated_by === $employee?->id) {
            return true;
        }

        // 4. Check quyền truy cập dựa trên thực thể gắn với hóa đơn (Polymorphic)
        if ($invoice->invoiceable_type === \App\Models\Sales\Order::class) {
            $order = $invoice->invoiceable;

            // Check chi nhánh (đơn Online thì cho phép)
            if (!is_null($order->store_location_id) && $order->store_location_id !== $employee?->store_location_id) {
                return false;
            }

            // Check quyền quản lý chi nhánh
            if ($user->hasRole('Quản lý cửa hàng')) {
                return true;
            }

            // Check quyền sở hữu (người tiếp nhận đơn hàng)
            return is_null($order->accepted_by) || $order->accepted_by === $employee?->id;
        }

        if ($invoice->invoiceable_type === \App\Models\Booking\Booking::class) {
            $booking = $invoice->invoiceable;

            // Đối với Booking, chỉ cho phép nếu chính là Designer của booking đó
            return $booking->designer_id === $user->designer?->id;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::INVOICE['SELECT']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isEmployee() && $user->hasPermissionTo(Permission::INVOICE['SELECT'])) {
            return $this->canAccess($user, $invoice);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::INVOICE['CREATE']);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo(Permission::INVOICE['UPDATE'])
            && $this->canAccess($user, $invoice);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo(Permission::INVOICE['DELETE'])
            && $this->canAccess($user, $invoice);
    }
}
