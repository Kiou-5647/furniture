<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Hr\Employee;

class ResendShipmentAction
{
    public function execute(Shipment $originalShipment, ?Employee $performedBy = null): Shipment
    {
        if ($originalShipment->status !== ShipmentStatus::Cancelled) {
            throw new \RuntimeException('Chỉ có thể gửi lại đơn đã hủy.');
        }

        if ($originalShipment->items->isEmpty()) {
            throw new \RuntimeException('Đơn vận chuyển không có sản phẩm.');
        }

        $originalShipment->update([
            'status' => ShipmentStatus::Pending,
        ]);

        return $originalShipment->load(['items.orderItem', 'originLocation']);
    }
}
