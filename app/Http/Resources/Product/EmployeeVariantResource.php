<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'compared_at_price' => $this->compared_at_price,
            'build_cost' => $this->build_cost,
            'weight' => $this->weight ?? [],
            'dimensions' => $this->dimensions ?? [],
            'option_values' => $this->option_values ?? [],
            'features' => $this->features ?? [],
            'specifications' => $this->specifications ?? [],
            'care_instructions' => $this->care_instructions ?? [],
            'status' => $this->status,
            'primary_image_url' => $this->getFirstMediaUrl('primary_image'),
            'hover_image_url' => $this->getFirstMediaUrl('hover_image'),
            'gallery_urls' => $this->getMedia('gallery')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
            ])->toArray(),
            'dimension_image_url' => $this->getFirstMediaUrl('dimension_image'),
            'swatch_image_url' => $this->getFirstMediaUrl('swatch_image'),
            'swatch_image_thumb_url' => $this->getFirstMediaUrl('swatch_image', 'swatch'),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
