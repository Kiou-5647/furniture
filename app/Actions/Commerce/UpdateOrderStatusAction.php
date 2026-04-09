<?php

namespace App\Actions\Commerce;

use App\Enums\OrderStatus;
use App\Models\Commerce\Order;
use App\Models\Employee\Employee;

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
            OrderStatus::Pending => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing => [OrderStatus::Completed, OrderStatus::Cancelled],
            OrderStatus::Completed => [],
            OrderStatus::Cancelled => [],
        ];

        if (! in_array($to, $validTransitions[$from] ?? [])) {
            throw new \RuntimeException(
                "Không thể chuyển trạng thái từ {$from->label()} sang {$to->label()}."
            );
        }
    }
}
