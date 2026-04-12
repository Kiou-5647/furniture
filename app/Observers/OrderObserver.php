<?php

namespace App\Observers;

use App\Actions\Sales\CreateRefundRequestAction;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Order;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     *
     * When an order transitions to completed AND its invoice is overpaid
     * (due to returned items reducing amount_due below amount_paid),
     * auto-create a refund request (Pending) for the excess amount.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === OrderStatus::Completed) {
            $this->createRefundForOverpaidInvoice($order);
        }
    }

    /**
     * When order completes with an overpaid invoice, create refund for excess amount.
     */
    protected function createRefundForOverpaidInvoice(Order $order): void
    {
        $invoice = $order->invoices()->first();
        if (! $invoice || $invoice->amount_paid <= $invoice->amount_due) {
            return;
        }

        // Update invoice to overpaid
        if ($invoice->status !== InvoiceStatus::Overpaid) {
            $invoice->updateQuietly(['status' => InvoiceStatus::Overpaid]);
        }

        // Check if a refund already exists for this overpaid amount
        $overpaidAmount = $invoice->amount_paid - $invoice->amount_due;
        $existingRefund = $order->refunds()
            ->where('amount', '>=', $overpaidAmount)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->exists();

        if ($existingRefund) {
            return;
        }

        // Get the employee who accepted the order (or first available employee)
        $employee = $order->acceptedBy
            ?? Employee::whereHas('user', fn ($q) => $q->where('type', 'employee'))->first();

        if ($employee) {
            app(CreateRefundRequestAction::class)->execute(
                $invoice,
                $employee,
                'Hoàn tiền thừa (đơn hàng hoàn thành, thanh toán vượt mức)'
            );
        }
    }
}
