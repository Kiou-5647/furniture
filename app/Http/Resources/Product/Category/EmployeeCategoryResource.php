<?php

namespace App\Http\Resources\Product\Category;

use App\Http\Resources\Setting\Lookup\EmployeeLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'group' => new EmployeeLookupResource($this->whenLoaded('group')),

            'product_type' => $this->product_type->value,
            'product_type_label' => $this->product_type->label(),

            'room_id' => $this->room_id,
            'room' => new EmployeeLookupResource($this->whenLoaded('room')),

            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->getFirstMediaUrl('image'),
            'image_thumb_url' => $this->getFirstMediaUrl('image', 'thumb'),

            'is_active' => $this->is_active,
            'metadata' => $this->metadata ?? [],

            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
