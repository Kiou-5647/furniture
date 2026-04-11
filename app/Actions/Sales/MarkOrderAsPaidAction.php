<?php

namespace App\Actions\Sales;

use App\Actions\Fulfillment\CreateShipmentsAction;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Auth\User;
use App\Models\Employee\Employee;
use App\Models\Sales\Invoice;
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
                ->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
                ->first();
            $customerId = $admin?->id;

            if (! $customerId) {
                throw new \RuntimeException('Không tìm thấy khách hàng hoặc quản trị viên.');
            }
        }

        DB::transaction(function () use ($order, $performedBy, $customerId) {
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoiceable_type' => Order::class,
                'invoiceable_id' => $order->id,
                'type' => InvoiceType::Full,
                'amount_due' => $order->total_amount,
                'amount_paid' => $order->total_amount,
                'status' => InvoiceStatus::Paid,
                'validated_by' => $performedBy?->id,
            ]);

            // Create payment record (cash payment for in-store orders)
            $payment = Payment::create([
                'customer_id' => $customerId,
                'gateway' => 'cash',
                'transaction_id' => 'CASH-'.now()->format('YmdHis').'-'.substr(md5($order->id), 0, 8),
                'amount' => $order->total_amount,
                'status' => PaymentStatus::Successful,
                'gateway_payload' => null,
            ]);

            // Allocate payment to invoice
            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount_applied' => $order->total_amount,
            ]);

            // Mark order as paid
            $order->update(['paid_at' => now()]);

            // For shipping orders, auto-create shipments
            if ($order->shipping_method_id) {
                app(CreateShipmentsAction::class)->execute($order);
            }

            // For in-store orders without shipping, mark completed
            if (! $order->shipping_method_id && $order->status === OrderStatus::Processing) {
                app(CompleteOrderAction::class)->execute($order, $performedBy);
            }
        });

        return $order->refresh();
    }
}
