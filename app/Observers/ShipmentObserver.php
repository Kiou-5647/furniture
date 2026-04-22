<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Product\ProductVariant;

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

        if ($shipment->isDirty('status')) {
            $oldStatus = $shipment->getOriginal('status');
            $newStatus = $shipment->status;

            // Increment when delivered and order is complete
            if ($newStatus === ShipmentStatus::Delivered && $shipment->order?->status === OrderStatus::Completed) {
                $this->updateSalesCounts($shipment, 'increment');
            }

            // Decrement when a delivered shipment is returned or cancelled
            if ($oldStatus === ShipmentStatus::Delivered && in_array($newStatus, [ShipmentStatus::Cancelled, ShipmentStatus::Returned])) {
                $this->updateSalesCounts($shipment, 'decrement');
            }
        }
    }

    protected function updateSalesCounts(Shipment $shipment, string $direction): void
    {
        foreach ($shipment->items as $item) {
            if ($item->status !== ShipmentStatus::Delivered) continue;

            $variant = $item->orderItem->purchasable;
            if (!$variant instanceof ProductVariant) continue;

            $method = $direction === 'increment' ? 'increment' : 'decrement';
            $variant->$method('sales_count', $item->quantity_shipped);

            // Bubble up the sales count
            $variant->productCard?->syncSalesCount();
            $variant->product?->syncSalesCount();
        }
    }

    protected function checkOrderCompletion(Shipment $shipment): void
    {
        $order = $shipment->order;

        // Check if ALL shipment items across ALL shipments are either delivered or returned
        $allResolved = $order->shipments()
            ->whereHas('items', fn($q) => $q->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned]))
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
