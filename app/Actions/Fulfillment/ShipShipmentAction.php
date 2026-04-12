<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Hr\Employee;
use App\Services\Location\OrderStockDeductionService;
use Illuminate\Support\Facades\DB;

class ShipShipmentAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(Shipment $shipment, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeShipped()) {
            throw new \RuntimeException('Đơn vận chuyển không thể gửi.');
        }

        // Deduct stock from source locations when shipment ships
        $this->stockDeductionService->deductStockForShipment($shipment, $performedBy);

        DB::transaction(function () use ($shipment, $performedBy) {
            // Mark all items as shipped
            $shipment->items()->update([
                'status' => ShipmentStatus::Shipped,
            ]);

            $shipment->update([
                'status' => ShipmentStatus::Shipped,
                'handled_by' => $performedBy?->id ?? $shipment->handled_by,
            ]);
        });

        return $shipment->refresh();
    }
}
