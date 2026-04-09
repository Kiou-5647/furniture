<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'price' => $this->calculateBundlePrice(),
            'items' => BundleItemResource::collection($this->whenLoaded('contents')),
            'image_url' => $this->getFirstMediaUrl('primary_image'),
            'image_thumb_url' => $this->getFirstMediaUrl('primary_image', 'thumb'),
        ];
    }
}
