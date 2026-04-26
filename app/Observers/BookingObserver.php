<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Services\Booking\BookingInvoiceService;
use Illuminate\Support\Facades\Log;

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
        if (! $booking->wasChanged('status')) {
            return;
        }

        $oldStatus = $booking->getOriginal('status');
        $newStatus = $booking->status;

        if (
            $newStatus === BookingStatus::Confirmed
            && $oldStatus !== BookingStatus::Confirmed
            && ! $booking->finalInvoice
        ) {
            $this->bookingInvoiceService->createFinalInvoice($booking);
        }
    }
}
