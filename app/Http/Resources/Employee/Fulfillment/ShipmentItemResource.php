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
            'order_item' => $this->whenLoaded('orderItem', fn () => [
                'id' => $this->orderItem->id,
                'purchasable_name' => $this->orderItem->purchasable?->name ?? '—',
            ]),
            'source_location' => $this->whenLoaded('sourceLocation', fn () => [
                'id' => $this->sourceLocation->id,
                'name' => $this->sourceLocation->name,
                'code' => $this->sourceLocation->code,
            ]),
            'quantity_shipped' => $this->quantity_shipped,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
        ];
    }
}
