<?php

namespace App\Actions\Sales;

use App\Enums\PaymentStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Payment;

class RefundPaymentAction
{
    public function execute(Payment $payment, ?Employee $performedBy = null): Payment
    {
        if ($payment->status !== PaymentStatus::Successful) {
            throw new \RuntimeException('Chỉ có thể hoàn tiền cho thanh toán thành công.');
        }

        $payment->update([
            'status' => PaymentStatus::Refunded,
        ]);

        return $payment->refresh();
    }
}
