<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;

class ShipmentObserver
{
    /**
     * Handle the Shipment "updated" event.
     * When a shipment is delivered, check if all items across all shipments
     * for this order are resolved (delivered or returned). If so, and if
     * payment is settled, mark the order as completed.
     */
    public function updated(Shipment $shipment): void
    {
        $wasNotDelivered = in_array($shipment->getOriginal('status'), [
            ShipmentStatus::Pending,
            ShipmentStatus::Shipped,
        ], true);

        if ($wasNotDelivered && $shipment->status === ShipmentStatus::Delivered || $shipment->status === ShipmentStatus::Returned) {
            $this->checkOrderCompletion($shipment);
        }
    }

    protected function checkOrderCompletion(Shipment $shipment): void
    {
        $order = $shipment->order;

        // Check if ALL shipment items across ALL shipments are either delivered or returned
        $allResolved = $order->shipments()
            ->whereHas('items', fn ($q) => $q->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned]))
            ->doesntExist();

        if (! $allResolved) {
            return;
        }

        // Check if order is paid
        if (! $order->isFullyPaid()) {
            return;
        }

        $order->update(['status' => OrderStatus::Completed]);
    }
}
