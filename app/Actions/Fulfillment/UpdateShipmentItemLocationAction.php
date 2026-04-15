<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use Illuminate\Support\Facades\DB;

class UpdateShipmentItemLocationAction
{
    public function execute(Shipment $shipment, ShipmentItem $shipmentItem, array $data): void
    {
        if ($shipmentItem->shipment_id !== $shipment->id) {
            throw new \InvalidArgumentException('Sản phẩm không thuộc đơn vận chuyển này.');
        }

        if ($shipment->status !== ShipmentStatus::Pending) {
            throw new \RuntimeException('Chỉ có thể đổi kho khi đơn chưa xuất kho.');
        }

        $newLocationId = $data['source_location_id'];
        $order = $shipment->order;

        DB::transaction(function () use ($shipment, $shipmentItem, $newLocationId, $order) {
            // Update the item's source location
            $shipmentItem->update([
                'source_location_id' => $newLocationId,
            ]);

            // Check if there's already a shipment for this order with the target location
            $targetShipment = $order->shipments()
                ->where('origin_location_id', $newLocationId)
                ->where('id', '!=', $shipment->id)
                ->first();

            if ($targetShipment) {
                // Move item to existing shipment with matching location
                $shipmentItem->update([
                    'shipment_id' => $targetShipment->id,
                ]);
            } elseif ($shipment->items()->count() === 1) {
                // Only one item in this shipment — just update the shipment's origin
                $shipment->update([
                    'origin_location_id' => $newLocationId,
                ]);
            } else {
                // Multiple items, no matching shipment — create a new shipment
                $newShipment = Shipment::create([
                    'order_id' => $order->id,
                    'shipment_number' => Shipment::generateShipmentNumber(),
                    'origin_location_id' => $newLocationId,
                    'shipping_method_id' => $order->shipping_method_id,
                    'status' => ShipmentStatus::Pending,
                ]);

                $shipmentItem->update([
                    'shipment_id' => $newShipment->id,
                ]);
            }

            // Clean up empty shipments
            $shipment->refresh();
            if ($shipment->items->isEmpty()) {
                $shipment->delete();
            }
        });
    }
}
