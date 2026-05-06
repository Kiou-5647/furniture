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
use App\Support\CacheKeys;
use App\Support\CacheTag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function getFiltered(ProductFilterData $filter): LengthAwarePaginator
    {
        return Product::query()
            ->with(['vendor', 'category', 'collection', 'variants.inventories'])
            ->when($filter->vendor_id, fn($q) => $q->where('vendor_id', $filter->vendor_id))
            ->when($filter->category_id, fn($q) => $q->where('category_id', $filter->category_id))
            ->when($filter->collection_id, fn($q) => $q->where('collection_id', $filter->collection_id))
            ->when($filter->status, fn($q) => $q->byStatus($filter->status))
            ->when(! is_null($filter->is_new_arrival), fn($q) => $q->where('is_new_arrival', $filter->is_new_arrival))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(ProductFilterData $filter): LengthAwarePaginator
    {
        return Product::onlyTrashed()
            ->with(['vendor', 'category'])
            ->when($filter->vendor_id, fn($q) => $q->where('vendor_id', $filter->vendor_id))
            ->when($filter->category_id, fn($q) => $q->where('category_id', $filter->category_id))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    /**
     * Returns the materialized cards for a product so the employee
     * can see exactly how it is grouped on the storefront.
     */
    public function getProductCardAudit(Product $product): array
    {
        return $product->productCards()
            ->with(['options', 'variants'])
            ->get()
            ->map(fn($card) => [
                'card_id' => $card->id,
                'metrics' => [
                    'views' => $card->views_count,
                    'rating' => $card->average_rating,
                ],
                'non_swatch_options' => $card->options->pluck('slug', 'namespace'),
                'variant_count' => $card->variants()->count(),
                'variants' => $card->variants->pluck('sku'),
            ])->toArray();
    }

    public function getStatusOptions(): array
    {
        return ProductStatus::options();
    }

    public function getVendorOptions(): EloquentCollection
    {
        return Cache::tags([CacheTag::Vendors->value])
            ->remember(CacheTag::Vendors->key('options'), CacheKeys::TTL, fn() => Vendor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn(Vendor $vendor) => [
                    'id' => $vendor->id,
                    'label' => $vendor->name,
                ]));
    }

    public function getCategoryOptions(): EloquentCollection
    {
        return Cache::tags([CacheTag::Categories->value])
            ->remember(CacheTag::Categories->key('options'), CacheKeys::TTL, fn() => Category::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name'])
                ->map(fn(Category $category) => [
                    'id' => $category->id,
                    'label' => $category->display_name,
                ]));
    }

    public function getCollectionOptions(): EloquentCollection
    {
        return Cache::tags([CacheTag::Collections->value])
            ->remember(CacheTag::Collections->key('options'), CacheKeys::TTL, fn() => Collection::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name'])
                ->map(fn(Collection $collection) => [
                    'id' => $collection->id,
                    'label' => $collection->display_name,
                ]));
    }

    public function getLocationOptions(): EloquentCollection
    {
        return Cache::tags([CacheTag::Locations->value])
            ->remember(CacheTag::Locations->key('options'), CacheKeys::TTL, fn() => Location::query()
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'type', 'street', 'province_name', 'ward_name'])
                ->map(fn(Location $location) => [
                    'id' => $location->id,
                    'code' => $location->code,
                    'label' => $location->name,
                    'type' => $location->type->value,
                    'address' => $location->getFullAddress(),
                ]));
    }

    public function getVariantOptions(): EloquentCollection
    {
        return Cache::tags([CacheTag::VariantOptions->value])
            ->remember(CacheTag::VariantOptions->key('data'), CacheKeys::TTL, fn() => $this->buildVariantOptions());
    }

    protected function buildVariantOptions(): EloquentCollection
    {
        $namespaces = LookupNamespace::query()
            ->where('for_variants', true)
            ->where('is_active', true)
            ->with(['activeLookups' => fn($q) => $q->orderBy('display_name')])
            ->get();

        return $namespaces->map(fn($ns) => [
            'id' => $ns->id,
            'label' => $ns->display_name,
            'namespace' => $ns->slug,
            'options' => $ns->activeLookups->map(fn($lookup) => [
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
        return Cache::tags([CacheTag::FeatureOptions->value])
            ->remember(CacheTag::FeatureOptions->key('data'), CacheKeys::TTL, fn() => $this->buildFeatureOptions());
    }

    protected function buildFeatureOptions(): array
    {
        $ns = LookupNamespace::where('slug', 'tinh-nang')->first();
        if (! $ns) {
            return [];
        }

        return $ns->activeLookups()
            ->orderBy('display_name')
            ->get(['id', 'slug', 'display_name', 'description', 'metadata'])
            ->map(fn($lookup) => [
                'id' => $lookup->id,
                'slug' => $lookup->slug,
                'label' => $lookup->display_name,
                'description' => $lookup->description,
                'metadata' => $lookup->metadata ?? [],
            ])->values()->toArray();
    }

    public function getSpecNamespaces(): EloquentCollection
    {
        return Cache::tags([CacheTag::SpecNamespaces->value])
            ->remember(CacheTag::SpecNamespaces->key('data'), CacheKeys::TTL, fn() => LookupNamespace::query()
                ->whereNotIn('slug', ['tinh-nang', 'nhom-danh-muc'])
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'slug', 'display_name', 'for_variants'])
                ->map(fn($ns) => [
                    'id' => $ns->id,
                    'namespace' => $ns->slug,
                    'label' => $ns->display_name,
                    'for_variants' => $ns->for_variants,
                ])->values());
    }

    public function getSpecLookupOptions(string $namespace): EloquentCollection
    {
        return Cache::tags([CacheTag::SpecLookupPrefix->value . '.' . $namespace])
            ->remember(CacheKeys::getFiltersKeys('spec_lookup_options', $namespace), CacheKeys::TTL, fn() => $this->buildSpecLookupOptions($namespace));
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
            ->map(fn($lookup) => [
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
        return Cache::tags([CacheTag::AllSpecLookups->value])
            ->remember(CacheTag::AllSpecLookups->key('data'), CacheKeys::TTL, fn() => $this->buildAllSpecLookupOptions());
    }

    protected function buildAllSpecLookupOptions(): EloquentCollection
    {
        $namespaces = LookupNamespace::query()
            ->whereNotIn('slug', ['tinh-nang', 'nhom-danh-muc'])
            ->where('is_active', true)
            ->with(['activeLookups' => fn($q) => $q->orderBy('display_name')])
            ->get();

        $result = [];
        foreach ($namespaces as $ns) {
            $result[$ns->slug] = $ns->activeLookups->map(fn($lookup) => [
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
}
