<?php

namespace App\Services\Product;

use App\Data\Product\ProductFilterData;
use App\Enums\ProductStatus;
use App\Models\Inventory\Location;
use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Product\Product;
use App\Models\Setting\LookupNamespace;
use App\Models\Vendor\Vendor;
use App\Services\Cache\CacheService;
use App\Support\CacheKeys;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public function getStatusOptions(): array
    {
        return ProductStatus::options();
    }

    public function getVendorOptions(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product('vendors'),
            CacheKeys::TTL,
            fn () => Vendor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Vendor $vendor) => [
                    'id' => $vendor->id,
                    'label' => $vendor->name,
                ])
        );
    }

    public function getCategoryOptions(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product('categories'),
            CacheKeys::TTL,
            fn () => Category::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'label' => $category->display_name,
                ])
        );
    }

    public function getCollectionOptions(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product('collections'),
            CacheKeys::TTL,
            fn () => Collection::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name'])
                ->map(fn (Collection $collection) => [
                    'id' => $collection->id,
                    'label' => $collection->display_name,
                ])
        );
    }

    public function getLocationOptions(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::inventory('locations'),
            CacheKeys::TTL,
            fn () => Location::query()
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'type', 'address_data', 'province_name', 'ward_name'])
                ->map(fn (Location $location) => [
                    'id' => $location->id,
                    'code' => $location->code,
                    'label' => $location->name,
                    'type' => $location->type->value,
                    'address' => $location->getFullAddress(),
                ])
        );
    }

    public function getVariantOptions(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product('variant_options'),
            CacheKeys::TTL,
            fn () => $this->buildVariantOptions()
        );
    }

    protected function buildVariantOptions(): EloquentCollection
    {
        $namespaces = LookupNamespace::query()
            ->where('for_variants', true)
            ->where('is_active', true)
            ->with(['activeLookups' => fn ($q) => $q->orderBy('display_name')])
            ->get();

        return $namespaces->map(fn ($ns) => [
            'id' => $ns->id,
            'label' => $ns->display_name,
            'namespace' => $ns->slug,
            'options' => $ns->activeLookups->map(fn ($lookup) => [
                'id' => $lookup->id,
                'slug' => $lookup->slug,
                'label' => $lookup->display_name,
                'metadata' => $lookup->metadata ?? [],
                'image_url' => $lookup->getFirstMediaUrl('image', 'webp') ?: null,
                'image_thumb_url' => $lookup->getFirstMediaUrl('image', 'thumb') ?: null,
            ])->values()->toArray(),
        ])->values();
    }

    public function getFeatureOptions(): array
    {
        return Cache::remember(
            CacheKeys::product('feature_options'),
            CacheKeys::TTL,
            fn () => $this->buildFeatureOptions()
        );
    }

    protected function buildFeatureOptions(): array
    {
        $ns = LookupNamespace::where('slug', 'tinh-nang')->first();
        if (! $ns) {
            return [];
        }

        return $ns->activeLookups()
            ->orderBy('display_name')
            ->get(['id', 'slug', 'display_name', 'metadata'])
            ->map(fn ($lookup) => [
                'id' => $lookup->id,
                'slug' => $lookup->slug,
                'label' => $lookup->display_name,
                'metadata' => $lookup->metadata ?? [],
            ])->values()->toArray();
    }

    public function getSpecNamespaces(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product('spec_namespaces'),
            CacheKeys::TTL,
            fn () => LookupNamespace::query()
                ->whereNotIn('slug', ['tinh-nang', 'nhom-danh-muc'])
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'slug', 'display_name', 'for_variants'])
                ->map(fn ($ns) => [
                    'id' => $ns->id,
                    'namespace' => $ns->slug,
                    'label' => $ns->display_name,
                    'for_variants' => $ns->for_variants,
                ])->values()
        );
    }

    public function getSpecLookupOptions(string $namespace): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product("spec_lookup_options.{$namespace}"),
            CacheKeys::TTL,
            fn () => $this->buildSpecLookupOptions($namespace)
        );
    }

    protected function buildSpecLookupOptions(string $namespace): EloquentCollection
    {
        $ns = LookupNamespace::where('slug', $namespace)->first();
        if (! $ns) {
            return collect();
        }

        return $ns->activeLookups()
            ->orderBy('display_name')
            ->get(['id', 'slug', 'display_name', 'metadata', 'description'])
            ->map(fn ($lookup) => [
                'id' => $lookup->id,
                'slug' => $lookup->slug,
                'label' => $lookup->display_name,
                'description' => $lookup->description,
                'metadata' => $lookup->metadata ?? [],
                'image_url' => $lookup->getFirstMediaUrl('image', 'webp') ?: null,
                'image_thumb_url' => $lookup->getFirstMediaUrl('image', 'thumb') ?: null,
            ])->values();
    }

    public function getAllSpecLookupOptions(): EloquentCollection
    {
        return Cache::remember(
            CacheKeys::product('all_spec_lookup_options'),
            CacheKeys::TTL,
            fn () => $this->buildAllSpecLookupOptions()
        );
    }

    protected function buildAllSpecLookupOptions(): EloquentCollection
    {
        $namespaces = LookupNamespace::query()
            ->whereNotIn('slug', ['tinh-nang', 'nhom-danh-muc'])
            ->where('is_active', true)
            ->with(['activeLookups' => fn ($q) => $q->orderBy('display_name')])
            ->get();

        $result = [];
        foreach ($namespaces as $ns) {
            $result[$ns->slug] = $ns->activeLookups->map(fn ($lookup) => [
                'id' => $lookup->id,
                'slug' => $lookup->slug,
                'label' => $lookup->display_name,
                'description' => $lookup->description,
                'metadata' => $lookup->metadata ?? [],
                'image_url' => $lookup->getFirstMediaUrl('image', 'webp') ?: null,
                'image_thumb_url' => $lookup->getFirstMediaUrl('image', 'thumb') ?: null,
            ])->values()->toArray();
        }

        return collect($result);
    }

    public function getFiltered(ProductFilterData $filter): LengthAwarePaginator
    {
        return Product::query()
            ->with(['vendor', 'category', 'collection', 'variants.inventories'])
            ->when($filter->vendor_id, fn ($q) => $q->where('vendor_id', $filter->vendor_id))
            ->when($filter->category_id, fn ($q) => $q->where('category_id', $filter->category_id))
            ->when($filter->collection_id, fn ($q) => $q->where('collection_id', $filter->collection_id))
            ->when($filter->status, fn ($q) => $q->byStatus($filter->status))
            ->when(! is_null($filter->is_featured), fn ($q) => $q->where('is_featured', $filter->is_featured))
            ->when(! is_null($filter->is_dropship), fn ($q) => $q->where('is_dropship', $filter->is_dropship))
            ->when(! is_null($filter->is_new_arrival), fn ($q) => $q->where('is_new_arrival', $filter->is_new_arrival))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(ProductFilterData $filter): LengthAwarePaginator
    {
        return Product::onlyTrashed()
            ->with(['vendor', 'category'])
            ->when($filter->vendor_id, fn ($q) => $q->where('vendor_id', $filter->vendor_id))
            ->when($filter->category_id, fn ($q) => $q->where('category_id', $filter->category_id))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function syncPriceRange(string $productId): void
    {
        /** @var App\Models\Product\Product $product */
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $product->updateQuietly([
            'min_price' => $product->variants()->min('price') ?? 0,
            'max_price' => $product->variants()->max('price') ?? 0,
        ]);
    }

    public function syncFilterableOptions(string $productId): void
    {
        $product = Product::with('variants')->find($productId);
        if (! $product) {
            return;
        }

        $filterableOptions = $this->collectFilterableOptions($product);

        $product->updateQuietly(['filterable_options' => $filterableOptions]);
    }

    protected function collectFilterableOptions(Product $product): array
    {
        $filterableOptions = [];

        foreach ($product->option_groups ?? [] as $group) {
            $groupKey = $group['namespace'] ?? strtolower($group['name']);
            $fallbackKey = strtolower($group['name']);
            $values = collect($group['options'] ?? [])
                ->filter(fn ($option) => $product->variants->contains(
                    fn ($v) => ($v->option_values[$groupKey] ?? $v->option_values[$fallbackKey] ?? null) === $option['value'],
                ))
                ->pluck('value')
                ->values()
                ->toArray();

            if (! empty($values)) {
                $filterableOptions[$groupKey] = $values;
            }
        }

        foreach ($product->specifications ?? [] as $group) {
            if (! ($group['is_filterable'] ?? false)) {
                continue;
            }
            $namespace = $group['lookup_namespace'] ?? null;
            if (! $namespace) {
                continue;
            }
            foreach ($group['items'] ?? [] as $item) {
                if ($slug = $item['lookup_slug'] ?? null) {
                    $filterableOptions[$namespace][] = $slug;
                }
            }
        }

        foreach ($product->features ?? [] as $feature) {
            if ($slug = $feature['lookup_slug'] ?? null) {
                $filterableOptions['tinh-nang'][] = $slug;
            }
        }

        foreach ($product->variants as $variant) {
            foreach ($variant->features ?? [] as $feature) {
                if ($slug = $feature['lookup_slug'] ?? null) {
                    $filterableOptions['tinh-nang'][] = $slug;
                }
            }
            foreach ($variant->specifications ?? [] as $group) {
                if (! ($group['is_filterable'] ?? false)) {
                    continue;
                }
                $namespace = $group['lookup_namespace'] ?? null;
                if (! $namespace) {
                    continue;
                }
                foreach ($group['items'] ?? [] as $item) {
                    if ($slug = $item['lookup_slug'] ?? null) {
                        $filterableOptions[$namespace][] = $slug;
                    }
                }
            }
        }

        foreach ($filterableOptions as &$vals) {
            $vals = array_values(array_unique($vals));
        }

        return $filterableOptions;
    }

    public function clearCache(): void
    {
        $this->cache->flushProducts();
    }
}
