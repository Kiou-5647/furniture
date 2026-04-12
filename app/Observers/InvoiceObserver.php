<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Models\Sales\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->updateInvoiceStatus($invoice);
        $this->updateOrderPaidAt($invoice);
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
}
