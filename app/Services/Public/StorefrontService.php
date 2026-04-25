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

        return $cards->map(fn(ProductCard $card) => [
            'type' => 'product',
            'id' => $card->id,
            'product' => [
                'name' => $card->product->name,
                'is_new_arrival' => $card->product->is_new_arrival,
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
                'sale_price' => $v->sale_price,
                'primary_image_url' => $v->getFirstMediaUrl('primary_image', 'webp') ?? $v->getFirstMediaUrl('primary_image'),
                'hover_image_url' => $v->getFirstMediaUrl('hover_image', 'webp') ?? $v->getFirstMediaUrl('hover_image'),
                'swatch_image_url' => $v->getFirstMediaUrl('swatch_image', 'webp') ?? $v->getFirstMediaUrl('swatch_image'),
                'name' => $v->name,
                'label' => $v->swatch_label,
            ]),
        ])->values();
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
        $injectionInterval = rand(2, 6);

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
