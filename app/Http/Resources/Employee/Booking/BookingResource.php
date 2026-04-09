<?php

namespace App\Http\Resources\Employee\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'designer' => $this->whenLoaded('designer', fn () => [
                'id' => $this->designer->id,
                'name' => $this->designer->display_name,
                'hourly_rate' => $this->designer->hourly_rate,
            ]),
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'type' => $this->service->type->value,
                'base_price' => $this->service->base_price,
            ]),
            'start_at' => $this->start_at?->format('d/m/Y H:i'),
            'end_at' => $this->end_at?->format('d/m/Y H:i'),
            'deadline_at' => $this->deadline_at?->format('d/m/Y'),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'accepted_by' => $this->whenLoaded('acceptedBy', fn () => $this->acceptedBy->full_name),
            'has_invoice' => $this->invoice !== null,
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
