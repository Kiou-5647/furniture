<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Booking\Booking;

class BookingPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.create');
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.confirm');
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.cancel');
    }

    public function openInvoice(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.open_invoice');
    }

    public function manage(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.manage');
    }
}
