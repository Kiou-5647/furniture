<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Booking\Booking;

class BookingPolicy
{
    public function approve(User $user, Booking $booking): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasPermissionTo('bookings.approve')) {
            return true;
        }

        if ($booking->designer) {
            return $booking->designer->user_id === $user->id
                || $booking->designer->employee_id === $user->employee?->id
                || $booking->designer->vendor_user_id === $user->vendorUser?->id;
        }

        return false;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.view');
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.update');
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('bookings.delete');
    }
}
