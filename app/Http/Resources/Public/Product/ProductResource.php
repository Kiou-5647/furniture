<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category?->id,
                'name' => $this->category?->display_name,
                'slug' => $this->category?->slug,
                'product_type' => [
                    'name' => $this->category?->product_type->label(),
                    'slug' => $this->category?->product_type->value
                ],
                'room' => $this->category->room ? [
                    'name' => $this->category->room->display_name,
                    'slug' => $this->category->room->slug,
                ] : null,
                'group' => $this->category->group ? [
                    'name' => $this->category->group->display_name,
                    'slug' => $this->category->group->slug,
                ] : null,
            ]),
            'collection' => $this->whenLoaded('collection', fn() => [
                'id' => $this->collection?->id,
                'name' => $this->collection?->display_name,
                'slug' => $this->collection?->slug,
            ]),
            'name' => $this->name,
            'slug' => $this->slug,
            'option_groups' => $this->option_groups ?? [],
            'views_count' => $this->views_count ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'average_rating' => $this->average_rating ?? 0,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'grouped_variants' => $this->getGroupedVariantOptions(),
        ];
    }
}
