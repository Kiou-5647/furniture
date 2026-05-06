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
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'name' => $this->customer_name ?? $this->customer->full_name ?? $this->customer->name,
                'email' => $this->customer_email ?? $this->customer->user?->email,
                'phone' => $this->customer_phone ?? $this->customer->phone,
            ]),
            'designer' => $this->whenLoaded('designer', fn() => [
                'id' => $this->designer->id,
                'name' => $this->designer->display_name,
                'hourly_rate' => $this->designer->hourly_rate,
            ]),
            'address' => [
                'province_code' => $this->province_code,
                'province_name' => $this->province_name,
                'ward_code' => $this->ward_code,
                'ward_name' => $this->ward_name,
                'street' => $this->street,
                'full_address' => $this->getFullAddress(),
            ],
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'total_price' => $this->total_price,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'accepted_by' => $this->whenLoaded('acceptedBy', fn() => $this->acceptedBy->full_name),
            'deposit_invoice' => $this->whenLoaded('depositInvoice', fn() => $this->depositInvoice ? [
                'id' => $this->depositInvoice->id,
                'invoice_number' => $this->depositInvoice->invoice_number,
                'amount_due' => $this->depositInvoice->amount_due,
                'status' => $this->depositInvoice->status->value,
            ] : null),
            'final_invoice' => $this->whenLoaded('finalInvoice', fn() => $this->finalInvoice ? [
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
