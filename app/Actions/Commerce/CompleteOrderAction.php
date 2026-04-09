<?php

namespace App\Actions\Commerce;

use App\Enums\OrderStatus;
use App\Models\Commerce\Order;
use App\Models\Employee\Employee;

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
