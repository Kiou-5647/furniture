<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantSwatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'price' => $this->price,
            'in_stock' => $this->getAvailableStock() > 0,
            // We include primary_image so the main view can update on hover
            'primary_image_url' => $this->getFirstMediaUrl('primary_image', 'webp'),
            'swatch_image_url' => $this->getFirstMediaUrl('swatch_image', 'swatch'),
        ];
    }
}
