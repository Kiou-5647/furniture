<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking\Booking;

class ConfirmBookingAction
{
    public function execute(Booking $booking): Booking
    {
        if (! $booking->canBeConfirmed()) {
            throw new \RuntimeException('Đặt lịch không thể xác nhận.');
        }

        $booking->update([
            'status' => BookingStatus::Confirmed,
        ]);

        return $booking->fresh(['depositInvoice', 'finalInvoice']);
    }
}
