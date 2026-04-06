<?php

namespace App\Http\Resources\Setting\Lookup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicLookupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'image_url' => $this->getFirstMediaUrl('image'),
            'image_thumb_url' => $this->getFirstMediaUrl('image', 'thumb'),
            'hex_code' => $this->when(isset($this->metadata['hex_code']) && $this->metadata['hex_code'], $this->metadata['hex_code']),
        ];
    }
}
