<?php

namespace App\Actions\Fulfillment;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;
use Illuminate\Support\Facades\DB;

class DeliverShipmentAction
{
    public function execute(Shipment $shipment, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeDelivered()) {
            throw new \RuntimeException('Đơn vận chuyển không thể giao.');
        }

        DB::transaction(function () use ($shipment, $performedBy) {
            // Mark all items as delivered
            $shipment->items()->update([
                'status' => ShipmentStatus::Delivered,
            ]);

            $shipment->update([
                'status' => ShipmentStatus::Delivered,
                'handled_by' => $performedBy?->id ?? $shipment->handled_by,
            ]);

            // Check if all items in ALL shipments for this order are delivered → complete order
            $this->checkOrderCompletion($shipment);
        });

        return $shipment->refresh();
    }

    protected function checkOrderCompletion(Shipment $shipment): void
    {
        $order = $shipment->order;

        // Check if ALL shipment items across ALL shipments are delivered
        $allItemsDelivered = $order->shipments()
            ->whereHas('items', fn ($q) => $q->where('status', '!=', ShipmentStatus::Delivered))
            ->doesntExist();

        if ($allItemsDelivered) {
            $order->update(['status' => OrderStatus::Completed]);
        }
    }
}
