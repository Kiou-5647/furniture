<?php

namespace App\Http\Resources\Setting\Lookup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLookupResource extends JsonResource
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
            'namespace' => $this->namespace->value,
            'namespace_label' => $this->namespace->label(),

            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_path' => $this->image_path,

            'is_active' => $this->is_active,
            'metadata' => $this->metadata ?? [],

            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }
}
