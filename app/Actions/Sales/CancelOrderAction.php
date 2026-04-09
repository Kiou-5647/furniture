<?php

namespace App\Actions\Sales;

use App\Enums\OrderStatus;
use App\Models\Employee\Employee;
use App\Models\Sales\Order;

class CancelOrderAction
{
    public function execute(Order $order, ?Employee $performedBy = null): Order
    {
        if (! $order->canBeCancelled()) {
            throw new \RuntimeException('Đơn hàng không thể hủy.');
        }

        $order->update([
            'status' => OrderStatus::Cancelled,
            'accepted_by' => $performedBy?->id ?? $order->accepted_by,
        ]);

        return $order->refresh();
    }
}
