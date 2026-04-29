<?php

namespace App\Http\Resources\Employee\Sales;

use App\Http\Resources\Employee\Fulfillment\ShipmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'name' => $this->customer->customer?->full_name ?? $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'shipping_province_code' => $this->province_code,
            'shipping_ward_code' => $this->ward_code,
            'shipping_province_name' => $this->province_name,
            'shipping_ward_name' => $this->ward_name,
            'shipping_street' => $this->street,
            'shipping_address_text' => $this->getShippingAddressText(),
            'total_amount' => $this->total_amount,
            'total_items' => $this->total_items,
            'source' => $this->source,
            'payment_method' => $this->payment_method?->value,
            'payment_method_label' => $this->payment_method?->label(),
            'store_location' => $this->whenLoaded('storeLocation', fn() => [
                'id' => $this->storeLocation->id,
                'name' => $this->storeLocation->name,
                'code' => $this->storeLocation->code,
            ]),
            'paid_at' => $this->paid_at?->format('d/m/Y H:i'),
            'shipping_cost' => $this->shipping_cost,
            'shipping_method_id' => $this->shipping_method_id,
            'shipping_method' => $this->whenLoaded('shippingMethod', fn() => [
                'id' => $this->shippingMethod->id,
                'name' => $this->shippingMethod->name,
                'estimated_delivery_days' => $this->shippingMethod->estimated_delivery_days,
            ]),
            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'guest_email' => $this->guest_email,
            'notes' => $this->notes,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'shipments' => ShipmentResource::collection($this->whenLoaded('shipments')),
            'refunds' => RefundResource::collection($this->whenLoaded('refunds')),
            'accepted_by' => $this->whenLoaded('acceptedBy', fn() => $this->acceptedBy->full_name),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'deleted_at' => $this->deleted_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
