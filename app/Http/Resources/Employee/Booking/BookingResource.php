<?php

namespace App\Http\Resources\Employee\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer_name ?? $this->customer?->full_name ?? $this->customer->name,
                'email' => $this->customer_email ?? $this->customer->email,
                'phone' => $this->customer_phone ?? $this->customer->phone,
            ]),
            'designer' => $this->whenLoaded('designer', fn () => [
                'id' => $this->designer->id,
                'name' => $this->designer->display_name,
                'hourly_rate' => $this->designer->hourly_rate,
                'auto_confirm_bookings' => $this->designer->auto_confirm_bookings,
            ]),
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'type' => $this->service->type->value,
                'base_price' => $this->service->base_price,
                'deposit_percentage' => $this->service->deposit_percentage,
                'estimated_hours' => $this->service->estimated_hours,
                'is_schedule_blocking' => $this->service->is_schedule_blocking,
            ]),
            'sessions' => $this->whenLoaded('sessions', fn () => $this->sessions->map(fn ($s) => [
                'id' => $s->id,
                'date' => $s->date,
                'start_hour' => $s->start_hour,
                'end_hour' => $s->end_hour,
            ])),
            'start_at' => $this->start_at?->format('d/m/Y H:i'),
            'end_at' => $this->end_at?->format('d/m/Y H:i'),
            'deadline_at' => $this->deadline_at?->format('d/m/Y'),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'accepted_by' => $this->whenLoaded('acceptedBy', fn () => $this->acceptedBy->full_name),
            'deposit_invoice' => $this->whenLoaded('depositInvoice', fn () => $this->depositInvoice ? [
                'id' => $this->depositInvoice->id,
                'invoice_number' => $this->depositInvoice->invoice_number,
                'amount_due' => $this->depositInvoice->amount_due,
                'status' => $this->depositInvoice->status->value,
            ] : null),
            'final_invoice' => $this->whenLoaded('finalInvoice', fn () => $this->finalInvoice ? [
                'id' => $this->finalInvoice->id,
                'invoice_number' => $this->finalInvoice->invoice_number,
                'amount_due' => $this->finalInvoice->amount_due,
                'status' => $this->finalInvoice->status->value,
            ] : null),
            'can_confirm' => $this->canBeConfirmed(),
            'can_cancel' => $this->canBeCancelled(),
            'can_pay_deposit' => $this->canPayDeposit(),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
