<?php

namespace App\Actions\Sales;

use App\Enums\OrderStatus;
use App\Models\Hr\Employee;
use App\Models\Sales\Order;
use App\Services\Location\OrderStockDeductionService;

class CompleteOrderAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(Order $order, ?Employee $performedBy = null): Order
    {
        if (! $order->canBeCompleted()) {
            throw new \RuntimeException('Đơn hàng không thể hoàn thành.');
        }

        // For in-store orders (no shipping): deduct stock on completion
        if (! $order->shipping_method_id) {
            $this->stockDeductionService->deductStockForInStore($order, $performedBy);
        }

        // For shipping orders: auto-complete happens when all shipments are delivered
        // This endpoint is for in-store completion only

        $order->update([
            'status' => OrderStatus::Completed,
            'accepted_by' => $performedBy?->id ?? $order->accepted_by,
        ]);

        return $order->refresh();
    }
}
