<?php

namespace App\Actions\Sales;

use App\Enums\RefundStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Invoice;
use App\Models\Sales\Refund;
use Illuminate\Support\Facades\DB;

class CreateRefundRequestAction
{
    public function execute(Invoice $invoice, Employee $requestedBy, ?string $reason = null): Refund
    {
        return DB::transaction(function () use ($invoice, $requestedBy, $reason) {
            $payment = $invoice->allocations->first()?->payment;

            // Refund amount: if overpaid, refund the excess; otherwise refund the full amount_paid
            $refundAmount = $invoice->amount_paid > $invoice->amount_due
                ? $invoice->amount_paid - $invoice->amount_due
                : $invoice->amount_paid;

            $refund = Refund::create([
                'order_id' => $invoice->invoiceable_id,
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
