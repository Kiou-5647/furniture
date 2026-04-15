<?php

namespace App\Http\Resources\Employee\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferItemResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant' => $this->whenLoaded('variant', fn () => [
                'id' => $this->variant->id,
                'sku' => $this->variant->sku,
                'name' => $this->variant->name,
                'product_name' => $this->variant->product?->name,
                'option_values' => $this->variant->option_values,
                'price' => $this->variant->price,
                'image_url' => $this->variant->getFirstMediaUrl('primary_image', 'thumb'),
                'full_image_url' => $this->variant->getFirstMediaUrl('primary_image'),
            ]),
            'quantity_shipped' => $this->quantity_shipped,
            'quantity_received' => $this->quantity_received,
        ];
    }
}
