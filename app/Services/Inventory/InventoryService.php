<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
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
                'quantity' => $inv->quantity,
            ])
            ->toArray();

        $totalQuantity = array_sum(array_column($inventories, 'quantity'));

        return [
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'name' => $variant->name,
            'locations' => $inventories,
            'totals' => [
                'quantity' => $totalQuantity,
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
                'name' => $inv->variant->name,
                'product_name' => $inv->variant->product?->name,
                'quantity' => $inv->quantity,
            ])
            ->toArray();

        $totalQuantity = array_sum(array_column($inventories, 'quantity'));

        return [
            'location_id' => $location->id,
            'location_code' => $location->code,
            'location_name' => $location->name,
            'variants' => $inventories,
            'totals' => [
                'quantity' => $totalQuantity,
            ],
        ];
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
            ->where('quantity', '<=', 0)
            ->get()
            ->sortBy('quantity');
    }

    /**
     * Calculate total value for a variant at a location using WAC.
     *
     * @param  ProductVariant  $variant  The product variant
     * @param  Location  $location  The location
     * @return float Total value (cost_per_unit × quantity)
     */
    public function calculateWACValue(ProductVariant $variant, Location $location): float
    {
        $inventory = Inventory::where('variant_id', $variant->id)
            ->where('location_id', $location->id)
            ->first();

        if (! $inventory || $inventory->quantity <= 0) {
            return 0.0;
        }

        return (float) $inventory->cost_per_unit * $inventory->quantity;
    }
}
