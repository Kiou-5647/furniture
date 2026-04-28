<?php

namespace App\Http\Resources\Employee\Fulfillment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentItemResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_item' => $this->whenLoaded('orderItem', fn() => [
                'id' => $this->orderItem->id,
                'purchasable_name' => $this->orderItem->purchasable?->name ?? '—',
                'purchasable_id' => $this->orderItem->purchasable_id,
            ]),
            'variant' => [
                'id' => $this->variant->id,
                'name' => $this->variant->product->name . ' ' . $this->variant->name,
                'sku' => $this->variant->sku,
            ],
            'quantity_shipped' => $this->quantity_shipped,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
        ];
    }
}
