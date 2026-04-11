<?php

namespace App\Actions\Sales;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Order;
use App\Services\Location\OrderStockDeductionService;

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

        // For in-store orders without shipping: restore stock if completed (stock was deducted)
        if (! $order->shipping_method_id && $order->status === OrderStatus::Completed) {
            $this->stockDeductionService->restoreStockForInStore($order, $performedBy);
        }

        // For shipping orders cancelled after shipping: restore shipment stock
        if ($order->shipping_method_id) {
            foreach ($order->shipments as $shipment) {
                if ($shipment->status === ShipmentStatus::Shipped) {
                    $this->stockDeductionService->restoreStockForShipment($shipment, $performedBy);
                }
            }
        }

        // Cancel all shipments for this order
        if ($order->shipping_method_id) {
            $order->shipments()->update(['status' => ShipmentStatus::Cancelled]);
        }

        $order->update([
            'status' => OrderStatus::Cancelled,
            'accepted_by' => $performedBy?->id ?? $order->accepted_by,
        ]);

        return $order->refresh();
    }
}
