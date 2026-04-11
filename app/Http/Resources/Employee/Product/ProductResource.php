<?php

namespace App\Http\Resources\Employee\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'category_id' => $this->category_id,
            'collection_id' => $this->collection_id,
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
            ]),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'display_name' => $this->category?->display_name,
            ]),
            'collection' => $this->whenLoaded('collection', fn () => [
                'id' => $this->collection?->id,
                'display_name' => $this->collection?->display_name,
            ]),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'name' => $this->name,
            'features' => $this->features ?? [],
            'care_instructions' => $this->care_instructions ?? [],
            'specifications' => $this->specifications ?? [],
            'option_groups' => $this->option_groups ?? [],
            'filterable_options' => $this->filterable_options ?? [],
            'assembly_info' => $this->assembly_info ?? [],
            'warranty_months' => $this->warranty_months,
            'view_count' => $this->view_count,
            'review_count' => $this->review_count,
            'average_rating' => $this->average_rating,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,
            'is_custom_made' => $this->is_custom_made,
            'published_date' => $this->published_date?->format('d/m/Y'),
            'new_arrival_until' => $this->new_arrival_until?->format('d/m/Y'),
            'variants_count' => $this->whenCounted('variants', $this->variants_count),
            'variants' => VariantResource::collection($this->variants),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
