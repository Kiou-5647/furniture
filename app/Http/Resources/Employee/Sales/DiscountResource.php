<?php

namespace App\Http\Resources\Employee\Sales;

use App\Models\Sales\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Discount $resource */
        $resource = $this->resource;

        // Get the mapping of Class => Label from the model
        $typeLabels = Discount::getDiscountableTypes();
        $typeLabel = $typeLabels[$resource->discountable_type] ?? 'Không xác định';

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'type' => $resource->type,
            'value' => $resource->value,
            'start_at' => $resource->start_at,
            'end_at' => $resource->end_at,
            'is_active' => $resource->is_active,

            'discountable_type_label' => $typeLabel,

            'discountable_name' => $resource->discountable?->display_name
                ?? $resource->discountable?->name
                ?? 'N/A',

            'discountable_id' => $resource->discountable_id,
            'discountable_type' => $resource->discountable_type,
            'created_at' => $resource->created_at,
        ];
    }
}
