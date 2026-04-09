<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Auth\User;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use Illuminate\Support\Facades\DB;

class ProcessPaymentAction
{
    public function execute(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $customer = User::findOrFail($data['customer_id']);

            $payment = Payment::create([
                'customer_id' => $customer->id,
                'gateway' => $data['gateway'],
                'transaction_id' => $data['transaction_id'],
                'amount' => $data['amount'],
                'status' => PaymentStatus::Successful,
                'gateway_payload' => $data['gateway_payload'] ?? null,
            ]);

            // Create allocations for each invoice
            foreach ($data['allocations'] as $allocationData) {
                /** @var Invoice $invoice */
                $invoice = Invoice::findOrFail($allocationData['invoice_id']);

                $payment->allocations()->create([
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $allocationData['amount'],
                ]);

                // Update invoice amount_paid
                Invoice::where('id', $invoice->id)->increment('amount_paid', $allocationData['amount']);
                $invoice->refresh();

                // Check if fully paid
                if ($invoice->isFullyPaid()) {
                    $invoice->update(['status' => InvoiceStatus::Paid]);
                }
            }

            return $payment->load(['allocations.invoice']);
        });
    }
}
