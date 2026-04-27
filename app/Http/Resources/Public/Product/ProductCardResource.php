<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matching_variants_count' => $this->matching_variants_count ?? 0,
            'default_variant_id' => $this->default_variant_id ?? null,
            'metrics' => [
                'views_count' => $this->views_count,
                'sales_count' => $this->sales_count,
                'reviews_count' => $this->reviews_count,
                'average_rating' => $this->average_rating,
            ],
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'is_new_arrival' => $this->product->is_new_arrival,
            ],
            'swatches' => $this->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'slug' => $v->slug,
                'name' => $v->name,
                'label' => $v->swatch_label,
                'price' => $v->price,
                'sale_price' => $v->getEffectivePrice(),
                'in_stock' => $v->getAvailableStock() > 0,
                'primary_image_url' => $v->getFirstMediaUrl('primary_image', 'webp') ?? $v->getFirstMediaUrl('primary_image') ?? null,
                'hover_image_url' => $v->getFirstMediaUrl('hover_image', 'webp') ?? $v->getFirstMediaUrl('hover_image') ?? null,
                'swatch_image_url' => $v->getFirstMediaUrl('swatch_image', 'swatch') ?? $v->getFirstMediaUrl('swatch_image') ?? null,
                'swatch_label' => $v->swatch_label,
            ]),
            'option_values' => $this->options->pluck('slug', 'namespace'),
        ];
    }
}
