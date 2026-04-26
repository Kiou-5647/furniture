<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Hr\Employee;

class CancelBookingAction
{
    public function __construct(
        private CreateBookingRefundAction $createRefund,
    ) {}

    public function execute(Booking $booking, ?Employee $performedBy = null): Booking
    {
        if (! $booking->canBeCancelled()) {
            throw new \RuntimeException('Đặt lịch không thể hủy.');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
        ]);

        // Create refund if deposit was paid and employee is cancelling
        if ($booking->hasDepositPaid() && $performedBy !== null) {
            $this->createRefund->execute($booking, $performedBy);
        }

        return $booking->refresh();
    }
}
