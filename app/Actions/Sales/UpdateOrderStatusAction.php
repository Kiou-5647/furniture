<?php

namespace App\Actions\Sales;

use App\Enums\OrderStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Order;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $newStatus, ?Employee $performedBy = null): Order
    {
        $this->validateTransition($order->status, $newStatus);

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
