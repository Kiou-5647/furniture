<?php

namespace App\Actions\Booking;

use App\Enums\RefundStatus;
use App\Models\Booking\Booking;
use App\Models\Hr\Employee;
use App\Models\Sales\Refund;
use Illuminate\Support\Facades\DB;

class CreateBookingRefundAction
{
    /**
     * Create a refund request for a cancelled booking's deposit.
     */
    public function execute(Booking $booking, Employee $requestedBy): Refund
    {
        if (! $booking->hasDepositPaid()) {
            throw new \RuntimeException('Đặt lịch chưa thanh toán đặt cọc.');
        }

        $depositInvoice = $booking->depositInvoice;
        $payment = $depositInvoice->allocations->first()?->payment;

        return DB::transaction(function () use ($booking, $depositInvoice, $payment, $requestedBy) {
            return Refund::create([
                'booking_id' => $booking->id,
                'invoice_id' => $depositInvoice->id,
                'payment_id' => $payment?->id,
                'amount' => $depositInvoice->amount_paid,
                'status' => RefundStatus::Pending,
                'reason' => 'Hủy đặt lịch #'.substr($booking->id, 0, 8),
                'requested_by' => $requestedBy->id,
            ]);
        });
    }
}
