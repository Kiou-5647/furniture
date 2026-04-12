<?php

namespace App\Actions\Sales;

use App\Enums\OrderStatus;
use App\Models\Hr\Employee;
use App\Models\Sales\Order;
use App\Services\Location\OrderStockDeductionService;

class UpdateOrderStatusAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(Order $order, OrderStatus $newStatus, ?Employee $performedBy = null): Order
    {
        $this->validateTransition($order->status, $newStatus);

        // For in-store orders (no shipping): deduct stock when order is completed
        if (! $order->shipping_method_id && $newStatus === OrderStatus::Completed) {
            $this->stockDeductionService->deductStockForInStore($order, $performedBy);
        }

        $order->update([
            'status' => $newStatus,
            'accepted_by' => $performedBy?->id ?? $order->accepted_by,
        ]);

        return $order->refresh();
    }

    protected function validateTransition(OrderStatus $from, OrderStatus $to): void
    {
        $validTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (! in_array($to->value, $validTransitions[$from->value] ?? [])) {
            throw new \RuntimeException(
                "Không thể chuyển trạng thái từ {$from->label()} sang {$to->label()}."
            );
        }
    }
}
