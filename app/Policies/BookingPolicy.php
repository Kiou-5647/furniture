<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Booking\Booking;

class BookingPolicy
{
    private function canAccess(User $user, Booking $booking): bool
    {
        // 1. Check quyền quản lý
        if ($user->hasRole('Quản lý')) {
            return true;
        }

        // 2. Check vai trò nhân viên
        if (!$user->isEmployee()) {
            return false;
        }

        // 3. Check xem liệu nhân viên có phải là designer của booking đó không.
        return $booking->designer_id === $user->designer?->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::BOOKING['SELECT']);
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isEmployee() && $user->hasPermissionTo(Permission::BOOKING['SELECT'])) {
            return $this->canAccess($user, $booking);
        }

        if ($user->isCustomer()) {
            return $booking->customer_id === $user->customer?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::BOOKING['CREATE']);
    }

    public function updateStatus(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo(Permission::BOOKING['UPDATE'])
            && $this->canAccess($user, $booking);
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $this->updateStatus($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $this->updateStatus($user, $booking);
    }

    public function openInvoice(User $user, Booking $booking): bool
    {
        return $this->updateStatus($user, $booking);
    }

    public function markAsPaid(User $user, Booking $booking): bool
    {
        return $this->updateStatus($user, $booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo(Permission::BOOKING['DELETE'])
            && $this->canAccess($user, $booking);
    }
}
