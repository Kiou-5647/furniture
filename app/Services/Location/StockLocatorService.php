<?php

namespace App\Services\Location;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Product\Bundle;
use Illuminate\Support\Collection;

class StockLocatorService
{
    public function __construct(
        protected LocationDistanceService $distanceService,
    ) {}

    /**
     * Check if a shipping order has a clear "nearest" location.
     * Returns null if employee should choose manually.
     */
    public function resolveBestLocation(
        string $purchasableType,
        string $purchasableId,
        ?string $customerProvinceCode = null,
        ?string $employeeLocationId = null
    ): ?string {
        $stockOptions = $this->findStockForItem(
            $purchasableType,
            $purchasableId,
            $customerProvinceCode
        );

        if ($stockOptions->isEmpty()) {
            return null;
        }

        // If employee's store has stock, auto-select it
        if ($employeeLocationId) {
            $storeStock = $stockOptions->firstWhere('location_id', $employeeLocationId);
            if ($storeStock) {
                return $employeeLocationId;
            }
        }

        // If only one location has stock, auto-select it
        if ($stockOptions->count() === 1) {
            return $stockOptions->first()['location_id'];
        }

        // If only one location has same-province priority (0), auto-select it
        $sameProvince = $stockOptions->where('distance_priority', 0);
        if ($sameProvince->count() === 1) {
            return $sameProvince->first()['location_id'];
        }

        // Multiple options, no clear winner → employee must choose
        return null;
    }

    /**
     * Get all available stock locations for manual selection.
     */
    public function getAllStockOptions(
        string $purchasableType,
        string $purchasableId,
        ?string $customerProvinceCode = null
    ): Collection {
        return $this->findStockForItem(
            $purchasableType,
            $purchasableId,
            $customerProvinceCode
        );
    }

    /**
     * Find stock for a specific purchasable across all locations, sorted by distance.
     *
     * @return Collection<array{location_id: string, location_name: string, location_code: string, available_qty: int, distance_priority: int}>
     */
    public function findStockForItem(
        string $purchasableType,
        string $purchasableId,
        ?string $customerProvinceCode = null,
        ?string $excludeLocationId = null,
        ?string $specificLocationId = null
    ): Collection {
        if ($purchasableType === Bundle::class) {
            return $this->findStockForBundle($purchasableId, $customerProvinceCode, $excludeLocationId, $specificLocationId);
        }

        // Inventory table uses variant_id directly, not polymorphic
        $query = Inventory::query()
            ->where('variant_id', $purchasableId)
            ->where('quantity', '>', 0)
            ->with(['location:id,name,code,province_code,type']);

        if ($excludeLocationId) {
            $query->where('location_id', '!=', $excludeLocationId);
        }

        // If a specific location is requested, only check that location
        if ($specificLocationId) {
            $query->where('location_id', $specificLocationId);
        }

        $inventories = $query->get();

        // Get all locations sorted by distance
        $closestLocations = $this->distanceService
            ->getClosestLocationsToAddress($customerProvinceCode)
            ->keyBy('id');

        $results = $inventories->map(function (Inventory $inv) use ($customerProvinceCode) {
            $location = $inv->location;
            if (! $location) {
                return null;
            }

            // Distance priority: 0 = same province, 1 = different province
            $priority = ($customerProvinceCode && $location->province_code === $customerProvinceCode) ? 0 : 1;

            return [
                'location_id' => $location->id,
                'location_name' => $location->name,
                'location_code' => $location->code,
                'available_qty' => $inv->quantity,
                'distance_priority' => $priority,
            ];
        })->filter()->sortBy(['distance_priority' => 'asc']);

        return $results->values();
    }

    /**
     * Find locations that can fulfill the entire bundle.
     */
    protected function findStockForBundle(
        string $bundleId,
        ?string $customerProvinceCode = null,
        ?string $excludeLocationId = null,
        ?string $specificLocationId = null
    ): Collection {
        $bundle = Bundle::with('contents.product.variants.inventories.location')->find($bundleId);
        if (! $bundle) {
            return collect();
        }

        $locationAvailability = []; // [location_id => min_qty]

        foreach ($bundle->contents as $content) {
            $product = $content->product;
            if (! $product) {
                continue;
            }

            // Find all variants of this product that have stock
            $variantStocks = []; // [location_id => total_qty_across_variants]
            foreach ($product->variants as $variant) {
                foreach ($variant->inventories as $inv) {
                    if ($inv->quantity <= 0) {
                        continue;
                    }
                    if ($excludeLocationId && $inv->location_id === $excludeLocationId) {
                        continue;
                    }
                    if ($specificLocationId && $inv->location_id !== $specificLocationId) {
                        continue;
                    }

                    $variantStocks[$inv->location_id] = ($variantStocks[$inv->location_id] ?? 0) + $inv->quantity;
                }
            }

            // Only keep locations that have at least the required quantity for this component
            $eligibleLocations = array_filter($variantStocks, fn ($qty) => $qty >= $content->quantity);

            if (empty($eligibleLocations)) {
                return collect(); // Bundle cannot be fulfilled anywhere
            }

            if (empty($locationAvailability)) {
                // First component: initialize availability
                foreach ($eligibleLocations as $locId => $qty) {
                    $locationAvailability[$locId] = (int) ($qty / $content->quantity);
                }
            } else {
                // Subsequent components: intersect and update min_qty
                foreach ($locationAvailability as $locId => $currentMin) {
                    if (! isset($eligibleLocations[$locId])) {
                        unset($locationAvailability[$locId]);
                    } else {
                        $qtyForThisComponent = (int) ($eligibleLocations[$locId] / $content->quantity);
                        $locationAvailability[$locId] = min($currentMin, $qtyForThisComponent);
                    }
                }
            }
        }

        // Transform results into the same format as findStockForItem
        $results = collect($locationAvailability)->map(function ($minQty, $locId) use ($customerProvinceCode) {
            $location = Location::find($locId);
            if (! $location) {
                return null;
            }

            $priority = ($customerProvinceCode && $location->province_code === $customerProvinceCode) ? 0 : 1;

            return [
                'location_id' => $location->id,
                'location_name' => $location->name,
                'location_code' => $location->code,
                'available_qty' => $minQty,
                'distance_priority' => $priority,
            ];
        })->filter()->sortBy(['distance_priority' => 'asc']);

        return $results->values();
    }

    /**
     * Resolve stock locations for multiple items, grouping by best-fit location.
     *
     * @param  array  $items  [{purchasable_type, purchasable_id, quantity}]
     * @return array{location_groups: array, needs_transfer: bool}
     */
    public function resolveStockLocations(
        array $items,
        ?string $customerProvinceCode = null,
        ?string $excludeLocationId = null
    ): array {
        $itemStocks = [];

        // Find stock for each item
        foreach ($items as $item) {
            $stock = $this->findStockForItem(
                $item['purchasable_type'],
                $item['purchasable_id'],
                $customerProvinceCode,
                $excludeLocationId
            );

            if ($stock->isEmpty()) {
                continue; // No stock available for this item
            }

            $itemStocks[] = [
                'purchasable_type' => $item['purchasable_type'],
                'purchasable_id' => $item['purchasable_id'],
                'quantity_needed' => $item['quantity'],
                'stock_options' => $stock,
            ];
        }

        // Group items by best-fit location
        $locationGroups = [];
        foreach ($itemStocks as $itemStock) {
            // Use the closest location with sufficient stock
            foreach ($itemStock['stock_options'] as $stockOption) {
                if ($stockOption['available_qty'] >= $itemStock['quantity_needed']) {
                    $locId = $stockOption['location_id'];
                    if (! isset($locationGroups[$locId])) {
                        $locationGroups[$locId] = [
                            'location_id' => $locId,
                            'location_name' => $stockOption['location_name'],
                            'location_code' => $stockOption['location_code'],
                            'items' => [],
                        ];
                    }

                    $locationGroups[$locId]['items'][] = [
                        'purchasable_type' => $itemStock['purchasable_type'],
                        'purchasable_id' => $itemStock['purchasable_id'],
                        'quantity' => $itemStock['quantity_needed'],
                    ];

                    break;
                }
            }
        }

        $locationGroups = array_values($locationGroups);
        $needsTransfer = count($locationGroups) > 1;

        return [
            'location_groups' => $locationGroups,
            'needs_transfer' => $needsTransfer,
        ];
    }
}
