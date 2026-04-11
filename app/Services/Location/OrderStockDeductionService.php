<?php

namespace App\Services\Location;

use App\Actions\Inventory\RecordStockMovementAction;
use App\Enums\StockMovementType;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;
use App\Models\Inventory\Location;
use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderStockDeductionService
{
    public function __construct(
        protected RecordStockMovementAction $recordStockMovement,
    ) {}

    /**
     * Deduct stock for in-store order (no shipping) when order is Completed.
     * All items are deducted from the store location.
     */
    public function deductStockForInStore(Order $order, ?Employee $performedBy = null): void
    {
        if (! $order->store_location_id) {
            return;
        }

        $location = Location::find($order->store_location_id);
        if (! $location) {
            return;
        }

        DB::transaction(function () use ($order, $performedBy, $location) {
            foreach ($order->items as $item) {
                $this->deductItem($item, $location, $order, $performedBy);
            }
        });
    }

    /**
     * Restore stock for in-store order when cancelled.
     */
    public function restoreStockForInStore(Order $order, ?Employee $performedBy = null): void
    {
        if (! $order->store_location_id) {
            return;
        }

        $location = Location::find($order->store_location_id);
        if (! $location) {
            return;
        }

        DB::transaction(function () use ($order, $performedBy, $location) {
            foreach ($order->items as $item) {
                $this->restoreItem($item, $location, $order, $performedBy);
            }
        });
    }

    /**
     * Deduct stock for a shipment when it's shipped.
     * Each shipment item is deducted from its source_location.
     */
    public function deductStockForShipment(
        Shipment $shipment,
        ?Employee $performedBy = null
    ): void {
        DB::transaction(function () use ($shipment, $performedBy) {
            foreach ($shipment->items as $shipmentItem) {
                $sourceLocation = $shipmentItem->sourceLocation
                    ?? $shipmentItem->orderItem?->sourceLocation;
                if (! $sourceLocation) {
                    continue;
                }

                $orderItem = $shipmentItem->orderItem;
                if (! $orderItem) {
                    continue;
                }

                $variants = $this->resolveVariantsFromItem($orderItem);
                foreach ($variants as [$variant, $qty]) {
                    $this->recordStockMovement->handle(
                        variant: $variant,
                        location: $sourceLocation,
                        type: StockMovementType::Sell,
                        quantity: $qty,
                        notes: 'Shipment '.$shipment->shipment_number,
                        performedBy: $performedBy,
                        referenceType: Shipment::class,
                        referenceId: $shipment->id,
                    );
                }
            }
        });
    }

    /**
     * Restore stock for a cancelled shipment.
     */
    public function restoreStockForShipment(
        Shipment $shipment,
        ?Employee $performedBy = null
    ): void {
        DB::transaction(function () use ($shipment, $performedBy) {
            foreach ($shipment->items as $shipmentItem) {
                $sourceLocation = $shipmentItem->sourceLocation
                    ?? $shipmentItem->orderItem?->sourceLocation;
                if (! $sourceLocation) {
                    continue;
                }

                $orderItem = $shipmentItem->orderItem;
                if (! $orderItem) {
                    continue;
                }

                $variants = $this->resolveVariantsFromItem($orderItem);
                foreach ($variants as [$variant, $qty]) {
                    $this->recordStockMovement->handle(
                        variant: $variant,
                        location: $sourceLocation,
                        type: StockMovementType::Return,
                        quantity: $qty,
                        notes: 'Shipment '.$shipment->shipment_number.' cancelled',
                        performedBy: $performedBy,
                        referenceType: Shipment::class,
                        referenceId: $shipment->id,
                    );
                }
            }
        });
    }

    /**
     * Deduct a single order item from a specific location.
     * Handles both variants and bundles (expanding bundle into its selected variants).
     */
    protected function deductItem(OrderItem $item, Location $location, Order $order, ?Employee $performedBy): void
    {
        $variants = $this->resolveVariantsFromItem($item);
        foreach ($variants as [$variant, $qty]) {
            $this->recordStockMovement->handle(
                variant: $variant,
                location: $location,
                type: StockMovementType::Sell,
                quantity: $qty,
                notes: 'Order '.$order->order_number,
                performedBy: $performedBy,
                referenceType: Order::class,
                referenceId: $order->id,
            );
        }
    }

    /**
     * Restore a single order item back to its location.
     */
    protected function restoreItem(OrderItem $item, Location $location, Order $order, ?Employee $performedBy): void
    {
        $variants = $this->resolveVariantsFromItem($item);
        foreach ($variants as [$variant, $qty]) {
            $this->recordStockMovement->handle(
                variant: $variant,
                location: $location,
                type: StockMovementType::Return,
                quantity: $qty,
                notes: 'Order '.$order->order_number.' cancelled',
                performedBy: $performedBy,
                referenceType: Order::class,
                referenceId: $order->id,
            );
        }
    }

    /**
     * Resolve the actual ProductVariant(s) from an order item.
     * For variants: returns [[variant, item->quantity]]
     * For bundles: expands bundle configuration into selected variants
     */
    protected function resolveVariantsFromItem(OrderItem $item): array
    {
        if ($item->purchasable_type === ProductVariant::class) {
            $variant = ProductVariant::find($item->purchasable_id);
            if (! $variant) {
                return [];
            }

            return [[$variant, $item->quantity]];
        }

        if ($item->purchasable_type === Bundle::class) {
            return $this->resolveBundleVariants($item);
        }

        return [];
    }

    /**
     * Expand a bundle order item into its selected variant components.
     * Returns array of [ProductVariant, quantity] pairs.
     */
    protected function resolveBundleVariants(OrderItem $bundleItem): array
    {
        $configuration = $bundleItem->configuration ?? [];
        $selectedVariants = $configuration['selected_variants'] ?? [];

        $results = [];
        foreach ($selectedVariants as $sv) {
            $variant = ProductVariant::find($sv['variant_id'] ?? null);
            if (! $variant) {
                continue;
            }
            $qty = ($sv['quantity'] ?? 1) * $bundleItem->quantity;
            $results[] = [$variant, $qty];
        }

        return $results;
    }
}
