<?php

namespace App\Http\Resources\Public\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
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
            'customer_name' => $this->customer->full_name ?? 'Anonymous',
            'review_date' => $this->updated_at->translatedFormat('d/m/Y'),
            'variant_name' => "{$this->variant->product->name} - {$this->variant->name}",
            'sku' => $this->variant->sku,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ];
    }
}
