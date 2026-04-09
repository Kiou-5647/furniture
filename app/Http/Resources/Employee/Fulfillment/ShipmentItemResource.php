<?php

namespace App\Http\Resources\Employee\Fulfillment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_item' => $this->whenLoaded('orderItem', fn () => [
                'id' => $this->orderItem->id,
                'purchasable_name' => $this->orderItem->purchasable?->name ?? '—',
            ]),
            'quantity_shipped' => $this->quantity_shipped,
        ];
    }
}
