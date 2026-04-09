<?php

namespace App\Actions\Sales;

use App\Enums\OrderStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Order;

class CompleteOrderAction
{
    public function execute(Order $order, ?Employee $performedBy = null): Order
    {
        if (! $order->canBeCompleted()) {
            throw new \RuntimeException('Đơn hàng không thể hoàn thành.');
        }

        $order->update([
            'status' => OrderStatus::Completed,
            'accepted_by' => $performedBy?->id ?? $order->accepted_by,
        ]);

        return $order->refresh();
    }
}
