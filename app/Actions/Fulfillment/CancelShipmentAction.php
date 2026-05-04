<?php

namespace App\Actions\Fulfillment;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Hr\Employee;
use App\Services\Location\OrderStockDeductionService;
use Illuminate\Support\Facades\DB;

class CancelShipmentAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(Shipment $shipment, ?Employee $performedBy = null): Shipment
    {

        if (! $shipment->canBeCancelled()) {
            throw new \RuntimeException('Đơn vận chuyển không thể hủy.');
        }

        DB::transaction(function () use ($shipment, $performedBy) {
            if ($shipment->status === ShipmentStatus::Shipped) {
                $this->stockDeductionService->restoreStockForShipment(
                    $shipment,
                    $performedBy
                );
            }

            $shipment->update([
                'status' => ShipmentStatus::Cancelled,
            ]);

            $items = $shipment->items();

            foreach ($items as $item) {
                $item->update([
                    'status' => ShipmentStatus::Cancelled,
                ]);
            }

            $order = $shipment->order;

            $allCancelled = $order->shipments()
                ->where('status', '!=', ShipmentStatus::Cancelled)
                ->exists() === false;

            if ($allCancelled) {
                $order->update([
                    'status' => OrderStatus::Cancelled,
                ]);
            } else {
                $order->update([
                    'status' => OrderStatus::Processing,
                ]);
            }
        });

        return $shipment->refresh();
    }
}
