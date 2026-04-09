<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Employee\Employee;

class CancelBookingAction
{
    public function execute(Booking $booking, ?Employee $performedBy = null): Booking
    {
        if (! $booking->canBeCancelled()) {
            throw new \RuntimeException('Đặt lịch không thể hủy.');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'accepted_by' => $performedBy?->id,
        ]);

        return $booking->refresh();
    }
}
