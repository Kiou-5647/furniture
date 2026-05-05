<?php

namespace App\Http\Resources\Employee\Fulfillment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment_number,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status->value,
                'status_label' => $this->order->status->label(),
                'guest_name' => $this->order->guest_name,
                'guest_phone' => $this->order->guest_phone,
                'guest_email' => $this->order->guest_email,
                'customer' => [
                    'name' => $this->order->customer?->full_name ?? $this->order->customer?->name,
                    'email' => $this->order->customer?->user?->email,
                ],
                'shipping_address_text' => $this->order->getShippingAddressText(),
                'total_amount' => $this->order->total_amount,
                'shipping_cost' => $this->order->shipping_cost,
                'notes' => $this->order->notes,
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
            'handled_by' => $this->whenLoaded('handledBy', fn () => [
                'full_name' => $this->handledBy->full_name,
                'phone' => $this->handledBy->phone,
            ]),
            'can_ship' => $this->canBeShipped(),
            'can_deliver' => $this->canBeDelivered(),
            'can_cancel' => $this->canBeCancelled(),
            'can_resend' => $this->canBeResent(),
            'items' => ShipmentItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
