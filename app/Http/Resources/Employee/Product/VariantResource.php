<?php

namespace App\Http\Resources\Employee\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'swatch_label' => $this->swatch_label,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'profit_margin_value' => $this->profit_margin_value,
            'profit_margin_unit' => $this->profit_margin_unit,
            'weight' => $this->weight ?? [],
            'dimensions' => $this->dimensions ?? [],
            'option_values' => $this->option_values ?? [],
            'features' => $this->features ?? [],
            'specifications' => $this->specifications ?? [],
            'care_instructions' => $this->care_instructions ?? [],
            'status' => $this->status,
            'primary_image_url' => $this->getFirstMediaUrl('primary_image'),
            'hover_image_url' => $this->getFirstMediaUrl('hover_image'),
            'gallery_urls' => $this->getMedia('gallery')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
            ])->toArray(),
            'dimension_image_url' => $this->getFirstMediaUrl('dimension_image'),
            'swatch_image_url' => $this->getFirstMediaUrl('swatch_image'),
            'swatch_image_thumb_url' => $this->getFirstMediaUrl('swatch_image', 'swatch'),
            'in_stock' => $this->getAvailableStock() > 0,
            'total_stock' => $this->getAvailableStock(),
            'stock' => $this->whenLoaded('inventories', fn() => $this->inventories->map(fn($inv) => [
                'location_id' => $inv->location_id,
                'quantity' => $inv->quantity,
                'cost_per_unit' => $inv->quantity > 0 && $inv->cost_per_unit > 0
                    ? (float) $inv->cost_per_unit
                    : null,
            ])->toArray()),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
