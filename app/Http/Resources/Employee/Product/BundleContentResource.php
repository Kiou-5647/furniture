<?php

namespace App\Http\Resources\Employee\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'min_price' => $this->product->min_price,
                'variants' => $this->product->variants->map(fn ($variant) => [
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
                ])->values()->toArray(),
            ]),
            'quantity' => $this->quantity,
            'unit_price' => $this->whenLoaded('product', fn () => $this->product->min_price),
            'subtotal' => $this->whenLoaded('product', fn () => (float) $this->product->min_price * $this->quantity),
        ];
    }
}
