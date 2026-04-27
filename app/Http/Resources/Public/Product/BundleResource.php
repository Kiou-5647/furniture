<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'starting_price' => $this->calculateBundlePrice(),
            'images' => [
                'primary' => $this->getFirstMediaUrl('primary_image'),
                'hover' => $this->getFirstMediaUrl('hover_image'),
            ],
            'items' => $this->contents->map(fn($content) => [
                'id' => $content->id,
                'quantity' => $content->quantity,
                'product_card_id' => $content->productCard->id,
                'product_name' => $content->productCard->product->name,
                'variants' => $content->productCard->variants->map(fn($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'slug' => $v->slug,
                    'name' => $v->name,
                    'swatch_label' => $v->swatch_label,
                    'price' => $v->price,
                    'sale_price' => $v->getEffectivePrice(),
                    'in_stock' => $v->getAvailableStock() > 0,
                    'images' => [
                        'swatch' => $v->getFirstMediaUrl('swatch_image', 'swatch'),
                        'primary' => [
                            'full' => $v->getFirstMediaUrl('primary_image', 'webp'),
                            'thumb' => $v->getFirstMediaUrl('primary_image', 'thumb'),
                        ],
                        'hover' => [
                            'full' => $v->getFirstMediaUrl('hover_image', 'webp'),
                            'thumb' => $v->getFirstMediaUrl('hover_image', 'thumb'),
                        ],
                        'dimension' => [
                            'full' => $v->getFirstMediaUrl('dimension_image', 'webp'),
                            'thumb' => $v->getFirstMediaUrl('dimension_image', 'thumb'),
                        ],
                        'gallery' => [
                            'full' => $v->getFirstMediaUrl('gallery', 'webp'),
                            'thumb' => $v->getFirstMediaUrl('gallery', 'thumb'),
                        ],
                    ]
                ]),
            ]),
        ];
    }
}
