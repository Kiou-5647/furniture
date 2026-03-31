<?php

namespace App\Http\Resources\Product\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_group' => $this->whenLoaded('group', fn () => $this->group->display_name),
            'category_type' => $this->product_type->label(),

            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->getFirstMediaUrl('image'),
            'image_thumb_url' => $this->getFirstMediaUrl('image', 'thumb'),

            'meta' => [
                'title' => $this->metadata['meta_title'] ?? $this->display_name,
                'description' => $this->metadata['meta_description'] ?? $this->description,
                'canonical' => $this->metadata['canonical'] ?? null,
                'robots' => $this->metadata['robots'] ?? 'index, follow',
            ],
        ];
    }
}
