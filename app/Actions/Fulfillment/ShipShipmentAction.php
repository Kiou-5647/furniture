<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;
use App\Services\Location\OrderStockDeductionService;

class ShipShipmentAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(Shipment $shipment, string $carrier, string $trackingNumber, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeShipped()) {
            throw new \RuntimeException('Đơn vận chuyển không thể gửi.');
        }

        // Deduct stock from source locations when shipment ships
        $this->stockDeductionService->deductStockForShipment($shipment, $performedBy);

        $shipment->update([
            'status' => ShipmentStatus::Shipped,
            'carrier' => $carrier,
            'tracking_number' => $trackingNumber,
            'handled_by' => $performedBy?->id ?? $shipment->handled_by,
        ]);

        return $shipment->refresh();
    }
}
