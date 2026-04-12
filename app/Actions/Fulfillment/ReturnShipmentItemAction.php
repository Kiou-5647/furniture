<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\ShipmentItem;
use App\Services\Location\OrderStockDeductionService;
use Illuminate\Support\Facades\DB;

class ReturnShipmentItemAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(ShipmentItem $shipmentItem, string $reason, ?Employee $performedBy = null): ShipmentItem
    {
        if (! in_array($shipmentItem->status, [ShipmentStatus::Shipped, ShipmentStatus::Delivered], true)) {
            throw new \RuntimeException('Chỉ có thể trả sản phẩm đã gửi hoặc đã giao.');
        }

        DB::transaction(function () use ($shipmentItem, $performedBy, $reason) {
            // Restore stock when item is returned
            $this->stockDeductionService->restoreStockForShipmentItem($shipmentItem, $performedBy, $reason);

            $shipmentItem->update([
                'status' => ShipmentStatus::Returned,
            ]);

            // Update parent shipment status based on item statuses
            $this->checkShipmentStatus($shipmentItem);
        });

        return $shipmentItem->refresh();
    }

    protected function checkShipmentStatus(ShipmentItem $returnedItem): void
    {
        $shipment = $returnedItem->shipment;

        $allReturned = $shipment->items()->where('status', '!=', ShipmentStatus::Returned)->exists() === false;
        $allDelivered = $shipment->items()->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned])->exists() === false;

        if ($allReturned) {
            $shipment->update(['status' => ShipmentStatus::Returned]);
        } elseif ($allDelivered) {
            $shipment->update(['status' => ShipmentStatus::Delivered]);
        }
    }
}
