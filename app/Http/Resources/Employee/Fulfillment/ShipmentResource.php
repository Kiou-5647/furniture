<?php

namespace App\Http\Resources\Employee\Fulfillment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment_number,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
            ]),
            'origin_location' => $this->whenLoaded('originLocation', fn () => [
                'id' => $this->originLocation->id,
                'name' => $this->originLocation->name,
            ]),
            'shipping_method' => $this->whenLoaded('shippingMethod', fn () => [
                'id' => $this->shippingMethod->id,
                'name' => $this->shippingMethod->name,
            ]),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'carrier' => $this->carrier,
            'tracking_number' => $this->tracking_number,
            'handled_by' => $this->whenLoaded('handledBy', fn () => $this->handledBy->name),
            'items' => ShipmentItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
