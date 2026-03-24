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
            'image_path' => $this->image_path,
            'hex_code' => $this->when(isset($this->metadata['hex_code']) && $this->metadata['hex_code'], $this->metadata['hex_code']),

            'meta' => [
                'title' => $this->metadata['meta_title'] ?? $this->display_name,
                'description' => $this->metadata['meta_description'] ?? $this->description,
                'canonical' => $this->metadata['canonical'] ?? url('/danh-muc/'.$this->slug),
                'robots' => $this->metadata['robots'] ?? 'index, follow',
            ],
        ];
    }
}
