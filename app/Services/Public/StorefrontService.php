<?php

namespace App\Services\Public;

use App\Data\Public\ProductCardFilterData;
use App\Enums\ProductSortType;
use App\Models\Product\Bundle;
use App\Models\Product\ProductCard;
use App\Models\Product\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class StorefrontService
{
    public function getTotalCount(ProductCardFilterData $filter): int
    {
        $pool = $this->fetchStorefrontPool();

        $count = $pool->filter(function ($variant) use ($filter) {
            if ($filter->min_price !== null && $variant->effective_price < $filter->min_price) return false;
            if ($filter->max_price !== null && $variant->effective_price > $filter->max_price) return false;

            foreach ($filter->filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];
                if (!$this->isVariantSatisfiedInPool($namespace, $slugArray, $variant)) {
                    return false;
                }
            }
            return true;
        })->count();

        return $count;
    }

    public function getPurchasables(ProductCardFilterData $filter): LengthAwarePaginator
    {
        $paginatedProducts = $this->getProductCards($filter);
        $products = $paginatedProducts->items();


        $bundles = Bundle::query()
            ->where('is_active', true)
            ->get()
            ->map(fn(Bundle $bundle) => [
                'type' => 'bundle',
                'id' => $bundle->id,
                'name' => $bundle->name,
                'slug' => $bundle->slug,
                'price' => $bundle->calculateBundlePrice(),
                'images' => [
                    'primary' => $bundle->getFirstMediaUrl('primary_image', 'webp'),
                    'hover' => $bundle->getFirstMediaUrl('hover_image', 'webp')
                ],
            ]);

        if ($bundles->isEmpty()) {
            return $paginatedProducts;
        }

        $result = collect();
        $bundleIndex = 0;
        $injectionInterval = rand(3, 10);

        foreach ($products as $index => $product) {
            $result->push($product);

            if (($index + 1) % $injectionInterval === 0 && $bundleIndex < $bundles->count()) {
                $result->push($bundles[$bundleIndex]);
                $bundleIndex++;
            }
        }

        return new LengthAwarePaginator(
            $result->values(),
            $paginatedProducts->total(),
            $paginatedProducts->perPage(),
            $paginatedProducts->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Fetch product cards that match the given filters.
     * Uses an in-memory pool to ensure 100% consistency with filter summaries.
     */
    public function getProductCards(ProductCardFilterData $filter): LengthAwarePaginator
    {
        $pool = $this->fetchStorefrontPool();

        // Identify variants that satisfy ALL active filters
        $matchingVariantIds = [];
        foreach ($pool as $variant) {
            $isMatch = true;

            if ($filter->min_price !== null && $variant->effective_price < $filter->min_price) {
                $isMatch = false;
            }
            if ($isMatch && $filter->max_price !== null && $variant->effective_price > $filter->max_price) {
                $isMatch = false;
            }

            foreach ($filter->filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];
                if (!$this->isVariantSatisfiedInPool($namespace, $slugArray, $variant)) {
                    $isMatch = false;
                    break;
                }
            }
            if ($isMatch) {
                $matchingVariantIds[] = $variant->variant_id;
            }
        }

        $totalVariants = count($matchingVariantIds);
        if ($totalVariants === 0) {
            return new LengthAwarePaginator([], 0, $filter->limit ?? 24);
        }

        $orderedCardIds = [];
        foreach ($pool as $variant) {
            if (in_array($variant->variant_id, $matchingVariantIds)) {
                $cardId = $variant->product_card_id;

                if ($cardId && !in_array($cardId, $orderedCardIds)) {
                    $orderedCardIds[] = $cardId;
                }
            }
        }

        $totalCards = count($orderedCardIds);
        $limit = $filter->limit > 0 ? $filter->limit : 24;
        $offset = ($filter->page - 1) * $limit;

        $slicedCardIds = array_slice($orderedCardIds, $offset, $limit);

        if (empty($slicedCardIds)) {
            return new LengthAwarePaginator([], 0, $filter->limit ?? 24);
        }

        $flatCardIds = collect($slicedCardIds)->flatten()->toArray();

        $cards = ProductCard::query()
            ->whereIn('id', $flatCardIds)
            ->with(['product', 'variants', 'options'])
            ->get();

        $cards = $cards->sortBy(fn($card) => array_search($card->id, $slicedCardIds));

        if ($filter->type === ProductSortType::HIGH_LOW || $filter->type === ProductSortType::LOW_HIGH) {
            $cards = $cards->sortBy(function ($card) use ($filter) {
                $prices = $card->variants->map(fn($v) => $v->getEffectivePrice())->toArray();
                $price = empty($prices) ? 0 : ($filter->type === ProductSortType::HIGH_LOW ? max($prices) : min($prices));
                return $filter->type === ProductSortType::HIGH_LOW ? -$price : $price;
            });
        } elseif ($filter->type === ProductSortType::NEWEST) {
            $cards = $cards->sortByDesc(function ($card) {
                return [
                    $card->product->is_new_arrival ? 1 : 0,
                    $card->product->published_date
                ];
            });
        } elseif ($filter->type === ProductSortType::POPULARITY) {
            $cards = $cards->sortByDesc('sales_count');
        }

        $finalCollection = $cards->map(fn(ProductCard $card) => $this->attachMatchingData($card, $filter))
            ->filter()
            ->values();

        // 6. Return the Paginator
        return new LengthAwarePaginator(
            $finalCollection,
            $totalCards,
            $filter->limit,
            $filter->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function attachMatchingData(ProductCard $card, ProductCardFilterData $filterData): array
    {
        $filters = $filterData->filters;
        $product = $card->product;


        $matchingVariants = $card->variants->filter(function ($variant) use ($filters, $filterData) {

            if ($filterData->min_price !== null && $variant->getEffectivePrice() < $filterData->min_price) return false;
            if ($filterData->max_price !== null && $variant->getEffectivePrice() > $filterData->max_price) return false;

            $variantObj = (object) [
                'option_values' => $variant->option_values,
                'features' => $variant->features,
                'specifications' => $variant->specifications,
                'prod_features' => $variant->product->features,
                'prod_specifications' => $variant->product->specifications,
                'category_id' => $variant->product->category_id,
                'category_slug' => $variant->product->category?->slug,
                'collection_id' => $variant->product->collection_id,
                'collection_slug' => $variant->product->collection?->slug,
                'filterable_options' => $variant->product->filterable_options,
            ];

            foreach ($filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];
                if (!$this->isVariantSatisfiedInPool($namespace, $slugArray, $variantObj)) {
                    return false;
                }
            }
            return true;
        });

        if ($matchingVariants->isEmpty()) {
            return [];
        }

        return [
            'type' => 'product',
            'id' => $card->id,
            'matching_variants_count' => $matchingVariants->count(),
            'matched_variant_ids' => $matchingVariants->pluck('id')->toArray(),
            'default_variant_id' => $matchingVariants->first()?->id,
            'product' => [
                'name' => $product->name,
                'is_new_arrival' => $product->is_new_arrival,
            ],
            'metrics' => [
                'sales_count' => $card->sales_count,
                'average_rating' => $card->average_rating,
                'reviews_count' => $card->reviews_count,
            ],
            'swatches' => $card->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'slug' => $v->slug,
                'price' => $v->price,
                'sale_price' => $v->getEffectivePrice(),
                'primary_image_url' => $v->getFirstMediaUrl('primary_image', 'webp') ?? $v->getFirstMediaUrl('primary_image'),
                'hover_image_url' => $v->getFirstMediaUrl('hover_image', 'webp') ?? $v->getFirstMediaUrl('hover_image'),
                'swatch_image_url' => $v->getFirstMediaUrl('swatch_image', 'swatch') ?? $v->getFirstMediaUrl('swatch_image') ?? null,
                'name' => $v->name,
                'label' => $v->swatch_label,
                'is_available' => $v->isInStock(),
            ]),
        ];
    }

    public function getFilterSummary(ProductCardFilterData $filter): array
    {
        $filterHash = md5(json_encode($filter->filters));
        $cacheKey = "summary:{$filterHash}";

        return \Illuminate\Support\Facades\Cache::tags([\App\Support\CacheTag::CategoryFilters->value])
            ->remember($cacheKey, now()->addHours(24), function () use ($filter) {
                $pool = $this->fetchStorefrontPool();
                $namespaces = \App\Models\Setting\LookupNamespace::where('slug', '!=', 'nhom-danh-muc')->get();
                $summary = [];

                $special = [
                    'danh-muc' => 'Danh mục',
                    'bo-suu-tap' => 'Bộ sưu tập',
                ];

                $allNamespaceSlugs = $namespaces->pluck('slug')->toArray();
                $allNamespaceLabels = $namespaces->pluck('display_name', 'slug')->toArray();

                foreach (array_merge($allNamespaceSlugs, array_keys($special)) as $nsSlug) {
                    $label = $allNamespaceLabels[$nsSlug] ?? $special[$nsSlug] ?? $nsSlug;
                    $otherFilters = collect($filter->filters)->except([$nsSlug])->toArray();

                    $filteredPool = $pool->filter(function ($variant) use ($otherFilters) {
                        foreach ($otherFilters as $ns => $slugs) {
                            $slugArray = is_array($slugs) ? $slugs : [$slugs];
                            if (!$this->isVariantSatisfiedInPool($ns, $slugArray, $variant)) {
                                return false;
                            }
                        }
                        return true;
                    });

                    if ($filteredPool->isEmpty()) continue;

                    $counts = [];
                    foreach ($filteredPool as $variant) {
                        $vals = $this->extractValuesFromVariant($nsSlug, $variant);
                        foreach ($vals as $val) {
                            $counts[$val] = ($counts[$val] ?? 0) + 1;
                        }
                    }

                    if (empty($counts)) continue;

                    // 3. Resolve labels and build options
                    $options = [];
                    if ($nsSlug === 'danh-muc') {
                        $lookups = \App\Models\Product\Category::whereIn('slug', array_keys($counts))->get();
                        foreach ($lookups as $l) $options[] = ['slug' => $l->slug, 'label' => $l->display_name, 'count' => $counts[$l->slug]];
                    } elseif ($nsSlug === 'bo-suu-tap') {
                        $lookups = \App\Models\Product\Collection::whereIn('slug', array_keys($counts))->get();
                        foreach ($lookups as $l) $options[] = ['slug' => $l->slug, 'label' => $l->display_name, 'count' => $counts[$l->slug]];
                    } else {
                        $nsModel = \App\Models\Setting\LookupNamespace::where('slug', $nsSlug)->first();
                        if ($nsModel) {
                            $options = $nsModel->activeLookups()->get()->map(fn($l) => [
                                'slug' => $l->slug,
                                'label' => $l->display_name,
                                'count' => $counts[$l->slug] ?? 0
                            ])->filter(fn($o) => $o['count'] > 0)->values()->toArray();
                        }
                    }

                    if (!empty($options)) {
                        $summary[] = ['namespace' => $nsSlug, 'label' => $label, 'options' => $options];
                    }
                }

                return $summary;
            });
    }

    private function isVariantSatisfiedInPool(string $namespace, array $slugs, $variant): bool
    {
        if ($namespace === 'danh-muc') {
            return in_array($variant->category_slug, $slugs);
        }
        if ($namespace === 'bo-suu-tap') {
            return in_array($variant->collection_slug, $slugs);
        }

        // 2. Option Values
        if (isset($variant->option_values[$namespace]) && in_array($variant->option_values[$namespace], $slugs)) {
            return true;
        }

        // 3. Features (tinh-nang)
        if ($namespace === 'tinh-nang') {
            $allFeatures = array_merge((array)($variant->features ?? []), (array)($variant->prod_features ?? []));
            foreach ($allFeatures as $feat) {
                if (isset($feat['lookup_slug']) && in_array($feat['lookup_slug'], $slugs)) return true;
            }
        } else {
            // 4. Specifications (Variant + Product)
            $allSpecs = array_merge((array)($variant->specifications ?? []), (array)($variant->prod_specifications ?? []));
            foreach ($allSpecs as $spec) {
                if (isset($spec['lookup_namespace']) && $spec['lookup_namespace'] === $namespace && ($spec['is_filterable'] ?? false)) {
                    $items = $spec['items'] ?? [];
                    foreach ($items as $item) {
                        if (isset($item['lookup_slug']) && in_array($item['lookup_slug'], $slugs)) return true;
                    }
                }
            }
        }

        return false;
    }

    private function extractValuesFromVariant(string $namespace, $variant): array
    {
        $values = [];
        $values = [];
        if ($namespace === 'danh-muc') {
            if ($variant->category_slug) $values[] = $variant->category_slug;
        } elseif ($namespace === 'bo-suu-tap') {
            if ($variant->collection_slug) $values[] = $variant->collection_slug;
        } elseif (isset($variant->option_values[$namespace])) {
            $values[] = $variant->option_values[$namespace];
        } elseif ($namespace === 'tinh-nang') {
            $allFeatures = array_merge((array)($variant->features ?? []), (array)($variant->prod_features ?? []));
            foreach ($allFeatures as $feat) {
                if (isset($feat['lookup_slug'])) $values[] = $feat['lookup_slug'];
            }
        } else {
            $allSpecs = array_merge((array)($variant->specifications ?? []), (array)($variant->prod_specifications ?? []));
            foreach ($allSpecs as $spec) {
                if (isset($spec['lookup_namespace']) && $spec['lookup_namespace'] === $namespace && ($spec['is_filterable'] ?? false)) {
                    $items = $spec['items'] ?? [];
                    foreach ($items as $item) {
                        if (isset($item['lookup_slug'])) $values[] = $item['lookup_slug'];
                    }
                }
            }
        }

        return array_unique($values);
    }

    private function fetchStorefrontPool(): \Illuminate\Support\Collection
    {
        $rawPool = \App\Models\Product\ProductVariant::query()
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('collections', 'products.collection_id', '=', 'collections.id')
            ->where('products.status', 'published')
            ->where('product_variants.status', 'active')
            ->whereNull('product_variants.deleted_at')
            ->select([
                'product_variants.id as variant_id',
                'product_variants.product_card_id as product_card_id',
                'product_variants.option_values',
                'product_variants.features',
                'product_variants.specifications',
                'products.id as product_id',
                'products.category_id',
                'categories.slug as category_slug',
                'products.collection_id',
                'collections.slug as collection_slug',
                'products.features as prod_features',
                'products.specifications as prod_specifications',
                'products.filterable_options',
            ])
            ->get();

        return $rawPool->map(function ($item) {
            $decode = function ($value) {
                if (is_string($value)) return json_decode($value, true) ?? [];
                return is_array($value) ? $value : [];
            };

            $variantModel = ProductVariant::find($item->variant_id);
            $effectivePrice = $variantModel ? $variantModel->getEffectivePrice() : 0;

            return (object) [
                'variant_id' => $item->variant_id,
                'product_card_id' => $item->product_card_id,
                'effective_price' => $effectivePrice,
                'product_id' => $item->product_id,
                'category_id' => $item->category_id,
                'category_slug' => $item->category_slug,
                'collection_id' => $item->collection_id,
                'collection_slug' => $item->collection_slug,
                'option_values' => $decode($item->option_values),
                'features' => $decode($item->features),
                'specifications' => $decode($item->specifications),
                'prod_features' => $decode($item->prod_features),
                'prod_specifications' => $decode($item->prod_specifications),
                'filterable_options' => $decode($item->filterable_options),
            ];
        });
    }
}
