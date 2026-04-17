<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public static $wrap = false;

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
            'name' => $this->name,
            'swatch_label' => $this->swatch_label ?? '',
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'in_stock' => $this->getAvailableStock() > 0,
            'images' => [
                'primary' => [
                    'full' => $this->getFirstMediaUrl('primary_image', 'webp'),
                    'thumb' => $this->getFirstMediaUrl('primary_image', 'thumb'),
                ],
                'hover' => [
                    'full' => $this->getFirstMediaUrl('hover_image', 'webp'),
                    'thumb' => $this->getFirstMediaUrl('hover_image', 'thumb'),
                ],
                'dimension' => [
                    'full' => $this->getFirstMediaUrl('dimension_image', 'webp'),
                    'thumb' => $this->getFirstMediaUrl('dimension_image', 'thumb'),
                ],
                'swatch' => [
                    'full' => $this->getFirstMediaUrl('swatch_image', 'webp'),
                    'thumb' => $this->getFirstMediaUrl('swatch_image', 'thumb'),
                    'swatch' => $this->getFirstMediaUrl('swatch_image', 'swatch'),
                ],
                'gallery' => $this->getMedia('gallery')->map(fn($media) => [
                    'full' => $media->getUrl('webp'),
                    'thumb' => $media->getUrl('thumb'),
                ])->toArray(),
            ]

        ];
    }
}
