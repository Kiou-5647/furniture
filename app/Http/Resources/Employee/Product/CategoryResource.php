<?php

namespace App\Http\Resources\Employee\Product;

use App\Http\Resources\Employee\Setting\LookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'group' => new LookupResource($this->whenLoaded('group')),

            'product_type' => $this->product_type->value,
            'product_type_label' => $this->product_type->label(),

            'rooms' => LookupResource::collection($this->whenLoaded('rooms'))->resolve(),

            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->getFirstMediaUrl('image', 'webp') ?? $this->getFirstMediaUrl('image'),
            'image_thumb_url' => $this->getFirstMediaUrl('image', 'thumb'),

            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
