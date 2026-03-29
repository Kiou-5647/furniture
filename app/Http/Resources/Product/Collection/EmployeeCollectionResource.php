<?php

namespace App\Http\Resources\Product\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeCollectionResource extends JsonResource
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
            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
