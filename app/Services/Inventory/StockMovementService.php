<?php

namespace App\Services\Inventory;

use App\Data\Inventory\StockMovementFilterData;
use App\Enums\StockMovementType;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Product\ProductVariant;
use App\Support\CacheKeys;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class StockMovementService
{
    public function getFiltered(StockMovementFilterData $filter): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with([
                'variant:id,sku,name,product_id',
                'variant.product:id,name',
                'location:id,code,name',
                'performedBy:id,full_name',
            ])
            ->when($filter->type, fn ($q) => $q->byType(StockMovementType::from($filter->type)))
            ->when($filter->location_id, fn ($q) => $q->byLocation($filter->location_id))
            ->when($filter->variant_id, fn ($q) => $q->byVariant($filter->variant_id))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when($filter->date_from && $filter->date_to, fn ($q) => $q->byDateRange($filter->date_from, $filter->date_to))
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTypeOptions(): array
    {
        return StockMovementType::options();
    }

    public function getLocationOptions(): array
    {
        return Cache::remember(
            CacheKeys::inventory('location_options_simple'),
            now()->addDay(),
            fn () => Location::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(fn (Location $location) => [
                    'id' => $location->id,
                    'label' => "{$location->code} - {$location->name}",
                ])
                ->toArray()
        );
    }

    public function getVariantOptions(): array
    {
        // Not caching variant options as they can grow very large and change frequently
        return ProductVariant::query()
            ->with('product:id,name')
            ->orderBy('sku')
            ->get(['id', 'sku', 'name', 'product_id'])
            ->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'label' => "{$variant->sku} - {$variant->name}",
                'product_name' => $variant->product?->name,
            ])
            ->toArray();
    }
}
