<?php

namespace App\Actions\Sales;

use App\Models\Auth\User;
use App\Models\Hr\Employee;
use App\Models\Sales\Order;
use App\Models\Sales\Payment;
use App\Models\Sales\PaymentAllocation;
use Illuminate\Support\Facades\DB;

class MarkOrderAsPaidAction
{
    public function execute(Order $order, ?Employee $performedBy = null): Order
    {
        if ($order->paid_at) {
            throw new \RuntimeException('Đơn hàng đã được thanh toán.');
        }

        // For orders with no customer (guest orders), assign to super admin as fallback
        $customerId = $order->customer_id;
        if (! $customerId) {
            $admin = User::where('type', 'employee')
                ->whereHas('roles', fn($q) => $q->where('name', 'Quản trị viên'))
                ->first();
            $customerId = $admin?->id;

            if (! $customerId) {
                throw new \RuntimeException('Không tìm thấy khách hàng hoặc quản trị viên.');
            }
        }

        DB::transaction(function () use ($order, $performedBy, $customerId) {
            // Find existing invoice (created when order was created)
            $invoice = $order->invoices()->first();
            if (! $invoice) {
                throw new \RuntimeException('Không tìm thấy hóa đơn.');
            }

            // Update invoice payment
            $paymentAmount = $invoice->amount_due;

            // Update invoice payment
            $invoice->update([
                'amount_paid' => $paymentAmount,
                'validated_by' => $performedBy?->id,
            ]);

            // InvoiceObserver will set order.paid_at when amount_paid >= amount_due
            // and auto-create refund if invoice becomes overpaid

            // Refresh order to get updated paid_at from observer
            $order->refresh();

            // Shipments are now created manually via "Tạo đơn vận chuyển" dialog

            // Create payment record (cash payment for in-store orders)
            $payment = Payment::create([
                'customer_id' => $customerId,
                'gateway' => 'cash',
                'transaction_id' => 'CASH-' . now()->format('YmdHis') . '-' . substr(md5($order->id), 0, 8),
                'amount' => $order->total_amount,
                'gateway_payload' => null,
            ]);

            // Allocate payment to invoice
            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount_applied' => $order->total_amount,
            ]);
        });

        return $order->refresh();
    }
}
