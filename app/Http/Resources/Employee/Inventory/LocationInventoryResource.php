<?php

namespace App\Http\Resources\Employee\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationInventoryResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->variant->sku,
            'product_name' => $this->variant->product->name,
            'variant_id' => $this->variant_id,
            'variant_name' => $this->variant->name,
            'quantity' => $this->quantity,
            'cost_per_unit' => $this->cost_per_unit,
            'total_value' => $this->quantity * $this->cost_per_unit,
            'primary_image' => $this->variant->getFirstMediaUrl('primary_image', 'thumb'),
        ];
    }
}
