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

        return $cards->map(fn(ProductCard $card) => $this->attachMatchingData($card, $filter))->values();
    }

    private function attachMatchingData(ProductCard $card, ProductCardFilterData $filter): array
    {
        $filters = $filter->filters;
        $product = $card->product;

        $matchingVariants = $card->variants->filter(function ($variant) use ($filters, $product) {
            foreach ($filters as $namespace => $slug) {
                if (!$this->isFilterSatisfied($namespace, $slug, $variant, $product)) {
                    return false;
                }
            }
            return true;
        });

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
            ]),
        ];
    }

    private function isFilterSatisfied(string $namespace, string $slug, $variant, $product): bool
    {
        // 1. Check Product Level (Global Specs)
        if ($namespace === 'tinh-nang') {
            if (collect($product->features ?? [])->contains('lookup_slug', $slug)) return true;
        } else {
            $spec = $product->specifications[$namespace] ?? null;
            if ($spec && ($spec['is_filterable'] ?? false) && collect($spec['items'] ?? [])->contains('lookup_slug', $slug)) {
                return true;
            }
        }

        // 2. Check Variant Level
        // Option Values (Swatches/Non-swatches)
        if (($variant->option_values[$namespace] ?? null) === $slug) return true;

        // Features (tinh-nang)
        if ($namespace === 'tinh-nang') {
            if (collect($variant->features ?? [])->contains('lookup_slug', $slug)) return true;
        } else {
            // Specifications
            foreach ($variant->specifications ?? [] as $data) {
                if (($data['lookup_namespace'] ?? null) === $namespace &&
                    ($data['is_filterable'] ?? false) &&
                    collect($data['items'] ?? [])->contains('lookup_slug', $slug)
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
