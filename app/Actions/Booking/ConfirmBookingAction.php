<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Hr\Employee;

class ConfirmBookingAction
{
    public function execute(Booking $booking, ?Employee $performedBy = null): Booking
    {
        if (! $booking->canBeConfirmed()) {
            throw new \RuntimeException('Đặt lịch không thể xác nhận.');
        }

        // Status transition triggers BookingObserver → auto-creates final invoice
        $booking->update([
            'status' => BookingStatus::Confirmed,
            'accepted_by' => $performedBy?->id,
        ]);

        return $booking->fresh(['depositInvoice', 'finalInvoice', 'sessions']);
    }
}
