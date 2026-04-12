<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\ShipmentItem;

class ShipmentItemObserver
{
    /**
     * Handle the ShipmentItem "updated" event.
     * When an item is returned, reduce the invoice amount_due by the item value.
     * If amount_paid > amount_due after reduction, mark invoice as overpaid.
     */
    public function updated(ShipmentItem $shipmentItem): void
    {
        $wasNotReturned = $shipmentItem->getOriginal('status') !== ShipmentStatus::Returned;

        if ($wasNotReturned && $shipmentItem->status === ShipmentStatus::Returned) {
            $this->reduceInvoiceAmount($shipmentItem);
            $this->updateShipmentStatus($shipmentItem);
        }
    }

    protected function reduceInvoiceAmount(ShipmentItem $shipmentItem): void
    {
        $orderItem = $shipmentItem->orderItem;
        if (! $orderItem) {
            return;
        }

        $invoice = $orderItem->order->invoices()->first();
        if (! $invoice || $invoice->amount_due <= 0) {
            return;
        }

        $itemValue = (float) $orderItem->unit_price * $shipmentItem->quantity_shipped;
        $invoice->decrement('amount_due', $itemValue);

        // Refresh to get updated values
        $invoice->refresh();

        // If amount_paid > amount_due after reduction, mark as overpaid
        if ($invoice->amount_paid > $invoice->amount_due && $invoice->status !== InvoiceStatus::Overpaid) {
            $invoice->update(['status' => InvoiceStatus::Overpaid]);
        }
    }

    protected function updateShipmentStatus(ShipmentItem $shipmentItem): void
    {
        $shipment = $shipmentItem->shipment;

        $allReturned = $shipment->items()->where('status', '!=', ShipmentStatus::Returned)->exists() === false;
        $allDelivered = $shipment->items()->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned])->exists() === false;

        if ($allReturned) {
            $shipment->update(['status' => ShipmentStatus::Returned]);
        } elseif ($allDelivered) {
            $shipment->update(['status' => ShipmentStatus::Delivered]);
        }
    }
}
