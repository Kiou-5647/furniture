<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Services\Booking\BookingInvoiceService;

class BookingObserver
{
    public function __construct(
        private BookingInvoiceService $bookingInvoiceService,
    ) {}

    /**
     * Handle Booking status transitions.
     */
    public function updated(Booking $booking): void
    {
        if (! $booking->isDirty('status')) {
            return;
        }

        $oldStatus = $booking->getOriginal('status');
        $newStatus = $booking->status->value;

        // When booking transitions to Confirmed → create final invoice (if schedule-blocking)
        if ($newStatus === BookingStatus::Confirmed->value
            && $oldStatus !== BookingStatus::Confirmed->value
            && $booking->service?->is_schedule_blocking
            && ! $booking->finalInvoice) {
            $this->bookingInvoiceService->createFinalInvoice($booking);
        }
    }
}
