<?php

namespace App\Actions\Booking;

use App\Models\Booking\Booking;
use App\Models\Hr\Employee;
use App\Models\Sales\Payment;
use App\Models\Sales\PaymentAllocation;
use Illuminate\Support\Facades\DB;

class MarkBookingAsPaidAction
{
    public function execute(Booking $booking, string $invoiceType, ?Employee $performedBy = null)
    {
        $invoice = ($invoiceType === 'deposit')
            ? $booking->depositInvoice
            : $booking->finalInvoice;

        if (!$invoice) throw new \RuntimeException('Hóa đơn không tồn tại.');

        return DB::transaction(function () use ($booking, $invoice, $performedBy) {
            // 1. Update Invoice
            $invoice->update([
                'amount_paid' => $invoice->amount_due,
                'validated_by' => $performedBy?->id,
            ]);

            // 2. Create Payment Record (Mirroring MarkOrderAsPaidAction)
            $payment = Payment::create([
                'customer_id' => $booking->customer_id,
                'gateway' => 'cash',
                'transaction_id' => 'BOOK-CASH-' . now()->format('YmdHis'),
                'amount' => $invoice->amount_due,
            ]);

            // 3. Allocate Payment to Invoice
            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount_applied' => $invoice->amount_due,
            ]);

            return $booking->refresh();
        });
    }
}
