<?php

namespace App\Actions\Commerce;

use App\Enums\OrderStatus;
use App\Models\Commerce\Order;
use App\Models\Employee\Employee;

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
