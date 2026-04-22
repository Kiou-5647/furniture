<?php

namespace App\Services\Public;

use App\Data\Public\ProductCardFilterData;
use App\Models\Product\ProductCard;
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

        // Transform to match the ProductCard type expected by ProductCard.vue
        return $cards->map(fn(ProductCard $card) => [
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
                'label' => $v->name,
            ]),
        ])->values();
    }
}
