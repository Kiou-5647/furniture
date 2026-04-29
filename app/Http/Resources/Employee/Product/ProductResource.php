<?php

namespace App\Http\Resources\Employee\Product;

use App\Http\Resources\Employee\Product\VariantResource;
use App\Http\Resources\Public\Product\ProductCardResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {


        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'category_id' => $this->category_id,
            'collection_id' => $this->collection_id,
            'vendor' => $this->whenLoaded('vendor', fn() => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category?->id,
                'display_name' => $this->category?->display_name,
            ]),
            'collection' => $this->whenLoaded('collection', fn() => [
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
            'assembly_info' => array_merge($this->assembly_info ?? [], ['manual_url' => $this->getFirstMediaUrl('manual_file') ?? '']),
            'warranty_months' => $this->warranty_months,
            'views_count' => $this->views_count,
            'reviews_count' => $this->reviews_count,
            'average_rating' => $this->average_rating,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,
            'published_date' => $this->published_date?->format('d/m/Y'),
            'new_arrival_until' => $this->new_arrival_until?->format('d/m/Y'),
            'variants_count' => $this->whenCounted('variants', $this->variants_count),
            'variants' => VariantResource::collection($this->variants),
            'product_cards' => ProductCardResource::collection($this->whenLoaded('productCards')),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
