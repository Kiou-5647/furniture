<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockValuation;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Collection;

class InventoryService
{
    /**
     * Get variant stock across all locations.
     *
     * @param  ProductVariant  $variant  The product variant
     * @return array Array of location stock data
     */
    public function getVariantStock(ProductVariant $variant): array
    {
        $inventories = Inventory::where('variant_id', $variant->id)
            ->with('location:id,code,name,type')
            ->get()
            ->map(fn (Inventory $inv) => [
                'location_id' => $inv->location_id,
                'location_code' => $inv->location->code,
                'location_name' => $inv->location->name,
                'location_type' => $inv->location->type->value,
                'quantity_on_hand' => $inv->quantity_on_hand,
                'quantity_reserved' => $inv->quantity_reserved,
                'quantity_available' => $inv->quantity_available,
                'reorder_level' => $inv->reorder_level,
                'is_low_stock' => $inv->isLowStock(),
                'is_out_of_stock' => $inv->isOutOfStock(),
            ])
            ->toArray();

        $totalOnHand = array_sum(array_column($inventories, 'quantity_on_hand'));
        $totalReserved = array_sum(array_column($inventories, 'quantity_reserved'));
        $totalAvailable = array_sum(array_column($inventories, 'quantity_available'));

        return [
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'title' => $variant->title,
            'locations' => $inventories,
            'totals' => [
                'quantity_on_hand' => $totalOnHand,
                'quantity_reserved' => $totalReserved,
                'quantity_available' => $totalAvailable,
            ],
        ];
    }

    /**
     * Get location stock summary.
     *
     * @param  Location  $location  The location
     * @return array Array of variant stock data at the location
     */
    public function getLocationStock(Location $location): array
    {
        $inventories = Inventory::where('location_id', $location->id)
            ->with(['variant.product:id,name,sku'])
            ->get()
            ->map(fn (Inventory $inv) => [
                'inventory_id' => $inv->id,
                'variant_id' => $inv->variant_id,
                'sku' => $inv->variant->sku,
                'title' => $inv->variant->title,
                'product_name' => $inv->variant->product?->name,
                'quantity_on_hand' => $inv->quantity_on_hand,
                'quantity_reserved' => $inv->quantity_reserved,
                'quantity_available' => $inv->quantity_available,
                'reorder_level' => $inv->reorder_level,
                'is_low_stock' => $inv->isLowStock(),
                'is_out_of_stock' => $inv->isOutOfStock(),
            ])
            ->toArray();

        $totalOnHand = array_sum(array_column($inventories, 'quantity_on_hand'));
        $totalReserved = array_sum(array_column($inventories, 'quantity_reserved'));
        $totalAvailable = array_sum(array_column($inventories, 'quantity_available'));

        return [
            'location_id' => $location->id,
            'location_code' => $location->code,
            'location_name' => $location->name,
            'variants' => $inventories,
            'totals' => [
                'quantity_on_hand' => $totalOnHand,
                'quantity_reserved' => $totalReserved,
                'quantity_available' => $totalAvailable,
            ],
        ];
    }

    /**
     * Get low stock items (available <= reorder_level).
     *
     * @param  Location|null  $location  Optional location filter
     * @return Collection Collection of low stock inventory items
     */
    public function getLowStockItems(?Location $location = null): Collection
    {
        return Inventory::query()
            ->when($location, fn ($query) => $query->where('location_id', $location->id))
            ->with(['variant.product', 'location'])
            ->get()
            ->filter(fn (Inventory $inv) => $inv->isLowStock())
            ->sortBy('quantity_available');
    }

    /**
     * Get out of stock items.
     *
     * @return Collection Collection of out of stock inventory items
     */
    public function getOutOfStockItems(): Collection
    {
        return Inventory::query()
            ->with(['variant.product', 'location'])
            ->get()
            ->filter(fn (Inventory $inv) => $inv->isOutOfStock())
            ->sortBy('quantity_available');
    }

    /**
     * Sync inventory available quantity.
     *
     * @param  Inventory  $inventory  The inventory to sync
     */
    public function syncAvailable(Inventory $inventory): void
    {
        $inventory->quantity_available = $inventory->quantity_on_hand - $inventory->quantity_reserved;
        $inventory->save();
    }

    /**
     * Calculate total FIFO value for a variant at a location.
     *
     * @param  ProductVariant  $variant  The product variant
     * @param  Location  $location  The location
     * @return float Total FIFO value
     */
    public function calculateFIFOValue(ProductVariant $variant, Location $location): float
    {
        $total = StockValuation::where('variant_id', $variant->id)
            ->where('location_id', $location->id)
            ->where('quantity_remaining', '>', 0)
            ->selectRaw('SUM(batch_cost * quantity_remaining) as total')
            ->value('total');

        return (float) ($total ?? 0);
    }
}
