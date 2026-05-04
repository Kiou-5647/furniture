<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Product\ProductVariant;
use App\Models\Sales\OrderItem;

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

        $oldStatus = $shipmentItem->getOriginal('status');
        $newStatus = $shipmentItem->status;

        if ($oldStatus !== $newStatus) {

            if ($newStatus === ShipmentStatus::Delivered && $oldStatus !== ShipmentStatus::Delivered) {
                $variant = ProductVariant::find($shipmentItem->variant_id);
                if ($variant) {
                    $variant->increment('sales_count', $shipmentItem->quantity_shipped);
                    $variant->productCard?->syncSalesCount();
                    $variant->product?->syncSalesCount();
                }
            }

            if ($newStatus === ShipmentStatus::Returned && $oldStatus === ShipmentStatus::Delivered) {
                $variant = ProductVariant::find($shipmentItem->variant_id);
                if ($variant) {
                    $variant->decrement('sales_count', $shipmentItem->quantity_shipped);
                    $variant->productCard?->syncSalesCount();
                    $variant->product?->syncSalesCount();
                }
            }
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

    protected function reduceInvoiceAmount(ShipmentItem $shipmentItem): void
    {
        $orderItem = $shipmentItem->orderItem;
        if (! $orderItem) {
            return;
        }

        $order = $orderItem->order; // Get the order reference
        $invoice = $order->invoices()->first();
        if (! $invoice || $invoice->amount_due <= 0) {
            return;
        }

        $itemValue = $this->calculateItemValue($orderItem, $shipmentItem);
        $invoice->decrement('amount_due', $itemValue);
        $order->decrement('total_amount', $itemValue);

        // Refresh to get updated values
        $invoice->refresh();

        // If amount_paid > amount_due after reduction, mark as overpaid
        if ($invoice->amount_paid > $invoice->amount_due && $invoice->status !== InvoiceStatus::Overpaid) {
            $invoice->update(['status' => InvoiceStatus::Overpaid]);
        }
    }

    protected function calculateItemValue(OrderItem $orderItem, ShipmentItem $shipmentItem): float
    {
        // Simple variant: use unit_price
        if ($orderItem->purchasable_type !== \App\Models\Product\Bundle::class) {
            return (float) $orderItem->unit_price * $shipmentItem->quantity_shipped;
        }

        $configuration = $orderItem->configuration ?? [];

        foreach ($configuration as $configValue) {
            if (is_array($configValue) && ($configValue['variant_id'] ?? null) === $shipmentItem->variant_id) {
                return (float) ($configValue['price'] ?? 0) * $shipmentItem->quantity_shipped;
            }
        }

        return 0.0;
    }
}
