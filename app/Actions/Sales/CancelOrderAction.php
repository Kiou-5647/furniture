<?php

namespace App\Actions\Sales;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Hr\Employee;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Services\Location\OrderStockDeductionService;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(Order $order, ?Employee $performedBy = null): Order
    {
        if (! $order->canBeCancelled()) {
            throw new \RuntimeException('Đơn hàng không thể hủy.');
        }

        DB::transaction(function () use ($order, $performedBy) {
            // Restore stock for completed in-store orders
            if (! $order->shipping_method_id && $order->status === OrderStatus::Completed) {
                $this->stockDeductionService->restoreStockForInStore($order, $performedBy);
            }

            // Cancel pending shipments for shipping orders
            if ($order->shipping_method_id) {
                $order->shipments()
                    ->whereIn('status', [ShipmentStatus::Pending])
                    ->update(['status' => ShipmentStatus::Cancelled]);
            }

            // Create refund requests for paid invoices
            if ($performedBy) {
                $orderNumber = $order->order_number;
                $order->invoices->each(function (Invoice $invoice) use ($performedBy, $orderNumber) {
                    if ($invoice->amount_paid > 0) {
                        app(CreateRefundRequestAction::class)->execute(
                            $invoice,
                            $performedBy,
                            'Hủy đơn hàng '.$orderNumber
                        );
                    }
                    $invoice->update(['status' => InvoiceStatus::Cancelled]);
                });
            }

            // Mark order as cancelled
            $order->update([
                'status' => OrderStatus::Cancelled,
                'accepted_by' => $performedBy?->id ?? $order->accepted_by,
            ]);
        });

        return $order->refresh();
    }
}
