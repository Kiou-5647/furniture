<?php

namespace App\Services\Inventory;

use App\Data\Inventory\InventoryFilterData;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryService
{
    /**
     * Get paginated and filtered inventory stock.
     */
    public function getFiltered(InventoryFilterData $filter): LengthAwarePaginator
    {
        return Inventory::query()
            ->with(['variant:id,sku,name,product_id', 'variant.product:id,name', 'location:id,code,name,type'])
            ->when($filter->location_id, fn ($q) => $q->where('location_id', $filter->location_id))
            ->when($filter->variant_id, fn ($q) => $q->where('variant_id', $filter->variant_id))
            ->when($filter->search, function ($q) use ($filter) {
                $q->whereHas('variant', function ($qVariant) use ($filter) {
                    $qVariant->where('sku', 'ilike', "%{$filter->search}%")
                        ->orWhere('name', 'ilike', "%{$filter->search}%")
                        ->orWhereHas('product', fn ($qProd) => $qProd->where('name', 'ilike', "%{$filter->search}%"));
                })->orWhereHas('location', function ($qLoc) use ($filter) {
                    $qLoc->where('name', 'ilike', "%{$filter->search}%")
                        ->orWhere('code', 'ilike', "%{$filter->search}%");
                });
            })
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

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
            ->with(['variant:id,sku,name,product_id', 'variant.product:id,name'])
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
