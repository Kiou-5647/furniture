<?php

namespace App\Services\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Commerce\Order;
use App\Models\Commerce\OrderItem;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FulfillmentRouterService
{
    public function routeOrder(Order $order): void
    {
        if ($order->shipments()->exists()) {
            return; // Already routed
        }

        DB::transaction(function () use ($order) {
            // Group order items by product vendor_id
            $itemsByVendor = $order->items
                ->groupBy(fn (OrderItem $item) => $item->purchasable?->vendor_id ?? 'host');

            foreach ($itemsByVendor as $vendorId => $vendorItems) {
                $this->createShipment($order, $vendorId, $vendorItems);
            }
        });
    }

    protected function createShipment(Order $order, mixed $vendorId, Collection $vendorItems): Shipment
    {
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'shipment_number' => Shipment::generateShipmentNumber(),
            'vendor_id' => $vendorId === 'host' ? null : $vendorId,
            'status' => ShipmentStatus::Pending,
        ]);

        foreach ($vendorItems as $item) {
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_item_id' => $item->id,
                'quantity_shipped' => $item->quantity,
            ]);
        }

        return $shipment;
    }
}
