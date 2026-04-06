<?php

namespace App\Services\Product;

use App\Data\Product\ProductFilterData;
use App\Models\Product\Category;
use App\Models\Product\Collection as ProductCollection;
use App\Models\Product\Product;
use App\Models\Setting\LookupNamespace;
use App\Models\Vendor\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function getVendorOptions(): Collection
    {
        return Cache::remember('services.product.vendors', now()->addHours(24), function () {
            return Vendor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn(Vendor $vendor) => [
                    'id' => $vendor->id,
                    'label' => $vendor->name,
                ]);
        });
    }

    public function getCategoryOptions(): Collection
    {
        return Cache::remember('services.product.categories', now()->addHours(24), function () {
            return Category::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name'])
                ->map(fn(Category $category) => [
                    'id' => $category->id,
                    'label' => $category->display_name,
                ]);
        });
    }

    public function getCollectionOptions(): Collection
    {
        return Cache::remember('services.product.collections', now()->addHours(24), function () {
            return ProductCollection::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name'])
                ->map(fn(ProductCollection $collection) => [
                    'id' => $collection->id,
                    'label' => $collection->display_name,
                ]);
        });
    }

    public function getVariantOptions(): Collection
    {
        return Cache::remember('services.product.variant_options', now()->addHours(24), function () {
            $namespaces = LookupNamespace::query()
                ->where('for_variants', true)
                ->where('is_active', true)
                ->with(['activeLookups' => fn($q) => $q->orderBy('display_name')])
                ->get();

            return $namespaces->map(function ($ns) {
                return [
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
                ];
            })->values();
        });
    }

    public function getFeatureOptions(): array
    {
        return Cache::remember('services.product.feature_options', now()->addHours(24), function () {
            $ns = LookupNamespace::where('slug', 'tinh-nang')->first();
            if (! $ns) {
                return [];
            }

            return $ns->activeLookups()
                ->orderBy('display_name')
                ->get(['id', 'slug', 'display_name', 'metadata'])
                ->map(fn($lookup) => [
                    'id' => $lookup->id,
                    'slug' => $lookup->slug,
                    'label' => $lookup->display_name,
                    'metadata' => $lookup->metadata ?? [],
                ])->values()->toArray();
        });
    }

    public function getSpecNamespaces(): Collection
    {
        return Cache::remember('services.product.spec_namespaces', now()->addHours(24), function () {
            return LookupNamespace::query()
                ->whereNotIn('slug', ['tinh-nang', 'nhom-danh-muc'])
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'slug', 'display_name', 'for_variants'])
                ->map(fn($ns) => [
                    'id' => $ns->id,
                    'namespace' => $ns->slug,
                    'label' => $ns->display_name,
                    'for_variants' => $ns->for_variants,
                ])->values();
        });
    }

    public function getSpecLookupOptions(string $namespace): Collection
    {
        return Cache::remember("services.product.spec_lookup_options.{$namespace}", now()->addHours(24), function () use ($namespace) {
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
        });
    }

    public function getAllSpecLookupOptions(): Collection
    {
        return Cache::remember('services.product.all_spec_lookup_options', now()->addHours(24), function () {
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
        });
    }

    public function getFiltered(ProductFilterData $filter): LengthAwarePaginator
    {
        return Product::query()
            ->with(['vendor', 'category', 'collection'])
            ->when($filter->vendor_id, fn($q) => $q->where('vendor_id', $filter->vendor_id))
            ->when($filter->category_id, fn($q) => $q->where('category_id', $filter->category_id))
            ->when($filter->collection_id, fn($q) => $q->where('collection_id', $filter->collection_id))
            ->when($filter->status, fn($q) => $q->byStatus($filter->status))
            ->when(! is_null($filter->is_featured), fn($q) => $q->where('is_featured', $filter->is_featured))
            ->when(! is_null($filter->is_dropship), fn($q) => $q->where('is_dropship', $filter->is_dropship))
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

    public function syncPriceRange(string $productId): void
    {
        /** @var App\Models\Product\Product $product */
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $minPrice = $product->variants()->min('price');
        $maxPrice = $product->variants()->max('price');

        $product->updateQuietly([
            'min_price' => $minPrice ?? 0,
            'max_price' => $maxPrice ?? 0,
        ]);
    }

    public function syncFilterableOptions(string $productId): void
    {
        $product = Product::with('variants')->find($productId);

        if (! $product) {
            return;
        }

        $filterableOptions = [];

        foreach ($product->option_groups ?? [] as $group) {
            $groupKey = $group['namespace'] ?? strtolower($group['name']);
            $fallbackKey = strtolower($group['name']);
            $values = collect($group['options'] ?? [])
                ->filter(fn($option) => $product->variants->contains(
                    fn($v) => ($v->option_values[$groupKey] ?? $v->option_values[$fallbackKey] ?? null) === $option['value'],
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
                $slug = $item['lookup_slug'] ?? null;
                if ($slug) {
                    $filterableOptions[$namespace][] = $slug;
                }
            }
        }

        foreach ($product->features ?? [] as $feature) {
            $slug = $feature['lookup_slug'] ?? null;
            if ($slug) {
                $filterableOptions['tinh-nang'][] = $slug;
            }
        }

        foreach ($product->variants as $variant) {
            foreach ($variant->features ?? [] as $feature) {
                $slug = $feature['lookup_slug'] ?? null;
                if ($slug) {
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
                    $slug = $item['lookup_slug'] ?? null;
                    if ($slug) {
                        $filterableOptions[$namespace][] = $slug;
                    }
                }
            }
        }

        foreach ($filterableOptions as &$vals) {
            $vals = array_values(array_unique($vals));
        }

        $product->updateQuietly(['filterable_options' => $filterableOptions]);
    }

    public static function clearCache(): void
    {
        Cache::forget('services.product.vendors');
        Cache::forget('services.product.categories');
        Cache::forget('services.product.collections');
        Cache::forget('services.product.variant_options');
        Cache::forget('services.product.feature_options');
        Cache::forget('services.product.spec_namespaces');
        Cache::forget('services.product.spec_lookup_options');
        Cache::forget('services.product.all_spec_lookup_options');
    }
}
