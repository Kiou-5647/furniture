<?php

namespace App\Actions\Booking;

use App\Models\Booking\Booking;
use App\Models\Hr\Employee;
use App\Services\Booking\BookingInvoiceService;

class PayDepositAction
{
    public function __construct(
        private BookingInvoiceService $invoiceService,
    ) {}

    public function execute(Booking $booking, ?Employee $performedBy = null): Booking
    {
        if (! $booking->canPayDeposit()) {
            throw new \RuntimeException('Đặt lịch không thể thanh toán đặt cọc.');
        }

        // Mark deposit paid → InvoiceObserver handles status transitions
        $this->invoiceService->markDepositPaid($booking);

        return $booking->fresh(['depositInvoice', 'finalInvoice', 'sessions']);
    }
}
