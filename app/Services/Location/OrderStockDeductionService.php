<?php

namespace App\Services\Location;

use App\Actions\Inventory\RecordStockMovementAction;
use App\Enums\StockMovementType;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Hr\Employee;
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

        /** @var Location $location */
        $location = Location::find($order->store_location_id);
        if (! $location) {
            return;
        }

        DB::transaction(function () use ($order, $performedBy, $location) {
            foreach ($order->items as $item) {
                // cost_per_unit is already recorded in CreateOrderAction for in-store orders.
                // We only need to perform the stock movement.
                $this->recordStockMovementForInStoreItem($item, $location, $order, $performedBy);
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

        /** @var Location $location */
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
            // Group by OrderItem to calculate total bundle cost if necessary
            $itemCosts = [];

            foreach ($shipment->items as $shipmentItem) {
                $sourceLocation = $shipment->originLocation
                    ?? $shipmentItem->orderItem?->sourceLocation;
                if (! $sourceLocation) {
                    continue;
                }

                $findId = $shipmentItem->variant_id ?? $shipmentItem->orderItem?->purchasable_id;

                /** @var ProductVariant $variant */
                $variant = ProductVariant::find($findId);
                if (! $variant) {
                    continue;
                }

                // Track cost per OrderItem for bundles
                if ($shipmentItem->orderItem) {
                    $orderItemId = $shipmentItem->orderItem->id;
                    $cost = ($variant->getAverageCostPerUnit() ?? 0) * $shipmentItem->quantity_shipped;
                    $itemCosts[$orderItemId] = ($itemCosts[$orderItemId] ?? 0) + $cost;
                }

                $this->recordStockMovement->handle(
                    variant: $variant,
                    location: $sourceLocation,
                    type: StockMovementType::Sell,
                    quantity: $shipmentItem->quantity_shipped,
                    notes: 'Shipment ' . $shipment->shipment_number,
                    performedBy: $performedBy,
                    referenceType: Shipment::class,
                    referenceId: $shipment->id,
                );
            }

            // Update OrderItems with the calculated cost
            foreach ($itemCosts as $orderItemId => $totalCost) {
                $orderItem = \App\Models\Sales\OrderItem::find($orderItemId);
                if ($orderItem) {
                    if ($orderItem->purchasable_type === \App\Models\Product\ProductVariant::class) {
                        $variant = \App\Models\Product\ProductVariant::find($orderItem->purchasable_id);
                        $orderItem->update(['cost_per_unit' => $variant?->getAverageCostPerUnit()]);
                    } else {
                        // It's a bundle. 
                        // Use the recorded costs in configuration if available, otherwise use the computed cost from this shipment.
                        $recordedCost = 0;
                        if (!empty($orderItem->configuration)) {
                            foreach ($orderItem->configuration as $config) {
                                if (isset($config['cost'])) {
                                    $recordedCost += $config['cost'] * ($config['quantity'] ?? 1);
                                }
                            }
                        }

                        if ($recordedCost > 0) {
                            $orderItem->update(['cost_per_unit' => $recordedCost]);
                        } else {
                            $shippedQty = \App\Models\Fulfillment\ShipmentItem::where('order_item_id', $orderItemId)
                                ->where('shipment_id', $shipment->id)
                                ->sum('quantity_shipped');
                            if ($shippedQty > 0) {
                                $orderItem->update(['cost_per_unit' => $totalCost / $shippedQty]);
                            }
                        }
                    }
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
                $this->restoreSingleShipmentItem($shipmentItem, $shipment, 'Shipment ' . $shipment->shipment_number . ' cancelled', $performedBy);
            }
        });
    }

    /**
     * Restore stock for a single returned shipment item.
     */
    public function restoreStockForShipmentItem(
        ShipmentItem $shipmentItem,
        ?Employee $performedBy = null,
        string $reason = ''
    ): void {
        DB::transaction(function () use ($shipmentItem, $performedBy, $reason) {
            $this->restoreSingleShipmentItem(
                $shipmentItem,
                $shipmentItem->shipment,
                'Item returned' . ($reason ? ': ' . $reason : ''),
                $performedBy
            );
        });
    }

    protected function restoreSingleShipmentItem(
        ShipmentItem $shipmentItem,
        Shipment $shipment,
        string $notes,
        ?Employee $performedBy
    ): void {
        $sourceLocation = $shipment->originLocation
            ?? $shipmentItem->orderItem?->sourceLocation;
        if (! $sourceLocation) {
            return;
        }

        $variantId = $shipmentItem->variant_id;

        /** @var ProductVariant $variant */
        $variant = ProductVariant::find($variantId);
        if (! $variant) {
            return;
        }

        // Determine the cost this item had when it was sold
        $cost = null;
        $orderItem = $shipmentItem->orderItem;
        if ($orderItem) {
            if ($orderItem->purchasable_type === ProductVariant::class) {
                $cost = $orderItem->cost_per_unit;
            } elseif ($orderItem->purchasable_type === Bundle::class && !empty($orderItem->configuration)) {
                foreach ($orderItem->configuration as $config) {
                    if (isset($config['variant_id']) && $config['variant_id'] === $variantId) {
                        $cost = $config['cost'] ?? null;
                        break;
                    }
                }
            }
        }

        $this->recordStockMovement->handle(
            variant: $variant,
            location: $sourceLocation,
            type: StockMovementType::Return,
            quantity: $shipmentItem->quantity_shipped,
            notes: $notes,
            performedBy: $performedBy,
            referenceType: Shipment::class,
            referenceId: $shipment->id,
            costPerUnit: $cost,
        );
    }

    /**
     * Deduct a single order item from a specific location.
     * Handles both variants and bundles (expanding bundle into its selected variants).
     */
    protected function recordStockMovementForInStoreItem(OrderItem $item, Location $location, Order $order, ?Employee $performedBy): void
    {
        $variants = $this->resolveVariantsFromItem($item);
        foreach ($variants as [$variant, $qty]) {
            $this->recordStockMovement->handle(
                variant: $variant,
                location: $location,
                type: StockMovementType::Sell,
                quantity: $qty,
                notes: 'Order ' . $order->order_number,
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
            // Calculate the specific cost for this variant at the time of sale
            $cost = null;
            if ($item->purchasable_type === ProductVariant::class) {
                $cost = $item->cost_per_unit;
            } elseif ($item->purchasable_type === Bundle::class && !empty($item->configuration)) {
                foreach ($item->configuration as $config) {
                    if (isset($config['variant_id']) && $config['variant_id'] === $variant->id) {
                        $cost = $config['cost'] ?? null;
                        break;
                    }
                }
            }

            $this->recordStockMovement->handle(
                variant: $variant,
                location: $location,
                type: StockMovementType::Return,
                quantity: $qty,
                notes: 'Order ' . $order->order_number . ' cancelled',
                performedBy: $performedBy,
                referenceType: Order::class,
                referenceId: $order->id,
                costPerUnit: $cost,
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
        $results = [];

        foreach ($configuration as $configValue) {
            if (is_array($configValue) && isset($configValue['variant_id'])) {
                $variant = ProductVariant::find($configValue['variant_id']);

                if ($variant) {
                    $qty = ($configValue['quantity'] ?? 1) * $bundleItem->quantity;
                    $results[] = [$variant, $qty];
                }
            }
        }

        return $results;
    }
}
