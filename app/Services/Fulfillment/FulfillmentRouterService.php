<?php

namespace App\Services\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class FulfillmentRouterService
{
    public function routeOrder(Order $order): void
    {
        if ($order->shipments()->exists()) {
            return; // Already routed
        }

        DB::transaction(function () use ($order) {
            // Group order items by source_location_id
            $itemsByLocation = [];

            foreach ($order->items as $item) {
                // For shipping orders, use source_location_id
                $locationId = $item->source_location_id;

                if (! $locationId) {
                    // Fallback: use order's store location
                    $locationId = $order->store_location_id;
                }

                if (! $locationId) {
                    continue; // Skip items with no source
                }

                if (! isset($itemsByLocation[$locationId])) {
                    $itemsByLocation[$locationId] = [];
                }

                $itemsByLocation[$locationId][] = $item;
            }

            // Create one shipment per source location
            foreach ($itemsByLocation as $locationId => $locationItems) {
                $this->createShipment($order, $locationId, $locationItems);
            }
        });
    }

    protected function createShipment(Order $order, string $locationId, array $items): Shipment
    {
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'shipment_number' => Shipment::generateShipmentNumber(),
            'origin_location_id' => $locationId,
            'status' => ShipmentStatus::Pending,
        ]);

        foreach ($items as $item) {
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_item_id' => $item->id,
                'source_location_id' => $locationId,
                'quantity_shipped' => $item->quantity,
            ]);
        }

        return $shipment;
    }
}
