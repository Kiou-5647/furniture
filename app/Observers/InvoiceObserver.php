<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;
use App\Services\Booking\BookingInvoiceService;

class InvoiceObserver
{
    public function __construct(
        private BookingInvoiceService $bookingInvoiceService,
    ) {}

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->updateInvoiceStatus($invoice);
        $this->updateOrderPaidAt($invoice);
        $this->handleBookingLifecycle($invoice);
    }

    /**
     * Update invoice status based on amount_paid vs amount_due.
     */
    protected function updateInvoiceStatus(Invoice $invoice): void
    {
        if ($invoice->isDirty('amount_paid') || $invoice->isDirty('amount_due')) {
            if ($invoice->amount_paid >= $invoice->amount_due && $invoice->amount_paid > 0) {
                $newStatus = $invoice->amount_paid > $invoice->amount_due
                    ? InvoiceStatus::Overpaid
                    : InvoiceStatus::Paid;

                if ($invoice->status !== $newStatus) {
                    $invoice->updateQuietly(['status' => $newStatus]);
                }
            }
        }
    }

    /**
     * If invoice is fully paid, set the order's paid_at timestamp.
     */
    protected function updateOrderPaidAt(Invoice $invoice): void
    {
        if ($invoice->invoiceable_type !== 'App\Models\Sales\Order') {
            return;
        }

        $order = $invoice->invoiceable;
        if (! $order) {
            return;
        }

        if ($invoice->amount_paid >= $invoice->amount_due && $invoice->amount_paid > 0) {
            if (! $order->paid_at) {
                $order->update(['paid_at' => now()]);
            }
        } else {
            $order->update(['paid_at' => null]);
        }
    }

    /**
     * Handle Booking lifecycle transitions triggered by invoice status changes.
     *
     * - Deposit invoice paid → PendingDeposit → PendingConfirmation/Confirmed
     * - Final invoice paid → Confirmed → Completed
     */
    protected function handleBookingLifecycle(Invoice $invoice): void
    {
        $invoiceable = $invoice->invoiceable;
        if (! $invoiceable instanceof Booking) {
            return;
        }

        if (! $invoice->isDirty('status')) {
            return;
        }

        // Deposit invoice just paid → transition booking status
        if ($invoice->type === InvoiceType::Deposit
            && $invoice->status === InvoiceStatus::Paid
            && $invoiceable->status === BookingStatus::PendingDeposit) {

            $designer = $invoiceable->designer;

            if ($designer?->auto_confirm_bookings) {
                $invoiceable->update(['status' => BookingStatus::Confirmed]);
            } else {
                $invoiceable->update(['status' => BookingStatus::PendingConfirmation]);
            }

            return;
        }

        // Final invoice just paid → complete booking
        if ($invoice->type === InvoiceType::FinalBalance
            && $invoice->status === InvoiceStatus::Paid
            && $invoiceable->status === BookingStatus::Confirmed) {

            $invoiceable->update(['status' => BookingStatus::Completed]);
        }
    }
}
