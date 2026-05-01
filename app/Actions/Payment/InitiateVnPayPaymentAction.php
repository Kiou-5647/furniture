<?php

namespace App\Actions\Payment;

use App\Enums\InvoiceStatus;
use App\Models\Sales\Invoice;
use App\Services\Payment\VnPayService;
use Illuminate\Support\Facades\DB;

class InitiateVnPayPaymentAction
{
    public function __construct(
        private VnPayService $vnPayService,
    ) {}

    /**
     * Initiate VNPay payment for an invoice.
     *
     * @return array{payment_url: string, txn_ref: string}
     */
    public function execute(Invoice $invoice): array
    {
        return DB::transaction(function () use ($invoice) {
            // Mark invoice as "open" (awaiting payment)
            if ($invoice->status->value === 'draft') {
                $invoice->status = InvoiceStatus::Open;
                $invoice->save();
            }

            $txnRef = $invoice->invoice_number . '_' . now()->format('YmdHis');

            // Determine order info text
            $modelClass = class_basename($invoice->invoiceable_type);
            $orderInfo = sprintf(
                'Thanh toan %s cho %s',
                $invoice->type->label(),
                $modelClass,
            );

            $paymentUrl = $this->vnPayService->buildPaymentUrl([
                'txn_ref' => $txnRef,
                'amount' => (float) $invoice->amount_due,
                'order_info' => $orderInfo,
                'order_type' => 'furniture',
                'locale' => 'vn',
            ]);

            return [
                'payment_url' => $paymentUrl,
                'txn_ref' => $txnRef,
            ];
        });
    }
}
