<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'product_slug' => $this->whenLoaded('product', fn () => $this->product->slug),
            'quantity' => $this->quantity,
            'variants' => $this->whenLoaded('product', fn () => $this->product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'name' => $variant->name,
                'option_values' => $variant->option_values ?? [],
                'price' => $variant->price,
                'primary_image_url' => $variant->getFirstMediaUrl('primary_image') ?: '',
                'hover_image_url' => $variant->getFirstMediaUrl('hover_image') ?: '',
                'swatch_image_url' => $variant->getFirstMediaUrl('swatch_image') ?: '',
                'primary_image_thumb_url' => $variant->getFirstMediaUrl('primary_image', 'thumb') ?: '',
                'hover_image_thumb_url' => $variant->getFirstMediaUrl('hover_image', 'thumb') ?: '',
                'swatch_image_thumb_url' => $variant->getFirstMediaUrl('swatch_image', 'swatch') ?: '',
            ])->values()->toArray()),
        ];
    }
}
