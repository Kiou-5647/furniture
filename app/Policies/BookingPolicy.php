<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Booking\Booking;

class BookingPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý lịch thiết kế');
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('Quản lý lịch thiết kế');
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('Quản lý lịch thiết kế');
    }

    public function openInvoice(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('Quản lý lịch thiết kế');
    }

    public function manage(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('Quản lý lịch thiết kế');
    }
}
