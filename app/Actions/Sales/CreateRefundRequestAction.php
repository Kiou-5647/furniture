<?php

namespace App\Actions\Sales;

use App\Enums\RefundStatus;
use App\Models\Hr\Employee;
use App\Models\Sales\Invoice;
use App\Models\Sales\Refund;
use Illuminate\Support\Facades\DB;

class CreateRefundRequestAction
{
    public function execute(Invoice $invoice, Employee $requestedBy, ?string $reason = null): Refund
    {
        return DB::transaction(function () use ($invoice, $requestedBy, $reason) {
            // Check if there is an existing pending refund request for this invoice
            $existingRefund = Refund::where('invoice_id', $invoice->id)
                ->where('status', RefundStatus::Pending)
                ->first();

            // Refund amount: if overpaid, refund the excess; otherwise refund the full amount_paid
            $refundAmount = $invoice->amount_paid > $invoice->amount_due
                ? $invoice->amount_paid - $invoice->amount_due
                : $invoice->amount_paid;

            if ($existingRefund) {
                // Update existing pending refund amount
                $existingRefund->update([
                    'amount' => $refundAmount,
                    'reason' => $reason ?? $existingRefund->reason,
                ]);

                return $existingRefund->load(['order', 'payment', 'requestedBy']);
            }

            $payment = $invoice->allocations->first()?->payment;

            $refund = Refund::create([
                'order_id' => $invoice->invoiceable_id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment?->id,
                'amount' => $refundAmount,
                'status' => RefundStatus::Pending,
                'reason' => $reason ?? 'Hủy đơn hàng '.$invoice->invoiceable?->order_number,
                'requested_by' => $requestedBy->id,
            ]);

            return $refund->load(['order', 'payment', 'requestedBy']);
        });
    }
}
