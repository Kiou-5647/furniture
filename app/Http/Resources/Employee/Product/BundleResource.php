<?php

namespace App\Http\Resources\Employee\Product;

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
            'is_available' => $this->is_available ?? false,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'images' => [
                'primary' => $this->getFirstMediaUrl('primary_image'),
                'hover' => $this->getFirstMediaUrl('hover_image'),
                'gallery' => $this->getMedia('gallery')->map(fn($media) => $media->getUrl()),
            ],
            'contents' => $this->contents->map(fn($content) => [
                'id' => $content->id,
                'quantity' => $content->quantity,
                'product_card' => [
                    'id' => $content->productCard->id,
                    'name' => $content->productCard->product->name,
                    'product' => [
                        'id' => $content->productCard->product->id,
                        'name' => $content->productCard->product->name,
                    ],
                    'variants' => $content->productCard->variants->map(fn($v) => [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'name' => $v->name,
                        'price' => $v->price,
                        'sale_price' => $v->sale_price,
                        'primary_image' => $v->getFirstMediaUrl('primary_image'),
                        'hover_image' => $v->getFirstMediaUrl('hover_image'),
                        'swatch_image' => $v->getFirstMediaUrl('swatch_image'),
                    ]),
                ],
            ]),
        ];
    }
}
