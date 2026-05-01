<?php

namespace App\Services\Public;

use App\Data\Public\ProductCardFilterData;
use App\Models\Product\ProductCard;
use App\Models\Product\Bundle;
use Illuminate\Support\Collection;

class StorefrontService
{
    public function getProductCards(ProductCardFilterData $filter): Collection
    {
        $query = ProductCard::query()
            ->whereHas('product', fn($q) => $q->active())
            ->with(['product', 'variants', 'options']);

        if ($filter->category) {
            $query->whereHas('product.categories', fn($q) => $q->where('slug', $filter->category));
        }

        $query = match ($filter->type) {
            'top_seller' => $query->orderByDesc('sales_count'),
            'new' => $query->join('products', 'product_cards.product_id', '=', 'products.id')
                ->orderByDesc('products.created_at')
                ->select('product_cards.*'),
            default => $query,
        };

        $cards = $query->limit($filter->limit)->get();

        return $cards->map(fn(ProductCard $card) => $this->attachMatchingData($card, $filter))->filter()->values();
    }

    private function attachMatchingData(ProductCard $card, ProductCardFilterData $filter): array
    {
        $filters = $filter->filters;
        $product = $card->product;

        // 1. Pre-calculate which filters are satisfied GLOBALLY by the product
        // This avoids re-checking product specs for every single variant
        $globalMatches = [];
        foreach ($filters as $namespace => $slugs) {
            $slugArray = is_array($slugs) ? $slugs : [$slugs];
            if ($this->isProductGloballySatisfied($namespace, $slugArray, $product)) {
                $globalMatches[$namespace] = true;
            }
        }

        // 2. Filter variants based on the remaining requirements
        $matchingVariants = $card->variants->filter(function ($variant) use ($filters, $product, $globalMatches) {
            foreach ($filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];

                // If the product already satisfies this filter globally, move to next namespace
                if (isset($globalMatches[$namespace])) {
                    continue;
                }

                // Otherwise, the SPECIFIC variant must satisfy this filter
                if (!$this->isVariantSatisfied($namespace, $slugArray, $variant)) {
                    return false;
                }
            }
            return true;
        });

        // 3. Determine if the card should be shown
        // If no variants match all filters, this card is not a match
        if ($matchingVariants->isEmpty()) {
            return []; // Return empty to be filtered out by the caller
        }

        return [
            'type' => 'product',
            'id' => $card->id,
            'matching_variants_count' => $matchingVariants->count(),
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

    private function isProductGloballySatisfied(string $namespace, array $slugs, $product): bool
    {
        if ($namespace === 'tinh-nang') {
            return collect($product->features ?? [])->whereIn('lookup_slug', $slugs)->isNotEmpty();
        }

        $spec = $product->specifications[$namespace] ?? null;
        if ($spec && ($spec['is_filterable'] ?? false)) {
            return collect($spec['items'] ?? [])->whereIn('lookup_slug', $slugs)->isNotEmpty();
        }

        return false;
    }

    private function isVariantSatisfied(string $namespace, array $slugs, $variant): bool
    {
        // 1. Check Option Values (The most common filter: color, material)
        if (in_array($variant->option_values[$namespace] ?? null, $slugs)) {
            return true;
        }

        // 2. Check Variant-specific Features
        if ($namespace === 'tinh-nang') {
            if (collect($variant->features ?? [])->whereIn('lookup_slug', $slugs)->isNotEmpty()) {
                return true;
            }
        } else {
            // 3. Check Variant-specific Specifications
            foreach ($variant->specifications ?? [] as $data) {
                if (($data['lookup_namespace'] ?? null) === $namespace &&
                    ($data['is_filterable'] ?? false) &&
                    collect($data['items'] ?? [])->intersect($slugs)->isNotEmpty()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPurchasables(ProductCardFilterData $filter): Collection
    {

        // 1. Get sorted products (Maintain the order from DTO)
        $products = $this->getProductCards($filter);

        // 2. Get active bundles (Remove metrics as requested)
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
            return $products;
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

        return $result->values();
    }
}
