<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;

class ShipShipmentAction
{
    public function execute(Shipment $shipment, string $carrier, string $trackingNumber, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeShipped()) {
            throw new \RuntimeException('Đơn vận chuyển không thể gửi.');
        }

        $shipment->update([
            'status' => ShipmentStatus::Shipped,
            'carrier' => $carrier,
            'tracking_number' => $trackingNumber,
            'handled_by' => $performedBy?->user_id,
        ]);

        return $shipment->refresh();
    }
}
