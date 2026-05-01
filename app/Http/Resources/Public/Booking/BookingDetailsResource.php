<?php

namespace App\Http\Resources\Public\Booking;

use App\Enums\InvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailsResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_price' => $this->total_price,
            'start_at' => $this->start_at->toDateTimeString(),
            'end_at' => $this->end_at->toDateTimeString(),
            'full_address' => $this->getFullAddress(),
            'notes' => $this->notes,
            'can_cancel' => $this->canBeCancelled(),

            'designer' => [
                'name' => $this->designer?->full_name,
                'avatar' => $this->designer?->getFirstMediaUrl('avatar', 'thumb'),
                'rate' => $this->designer?->hourly_rate,
                'bio' => $this->designer?->bio,
                'portfolio' => $this->designer?->portfolio,
            ],

            'deposit_invoice' => $this->depositInvoice ? [
                'id' => $this->depositInvoice->id,
                'invoice_number' => $this->depositInvoice->invoice_number,
                'amount_due' => $this->depositInvoice->amount_due,
                'amount_paid' => $this->depositInvoice->amount_paid,
                'status' => $this->depositInvoice->status->value,
                'status_label' => $this->depositInvoice->status->label(),
                'created_at' => $this->depositInvoice->created_at->toDateTimeString(),
            ] : null,

            'final_invoice' => $this->finalInvoice ? [
                'id' => $this->finalInvoice->id,
                'invoice_number' => $this->finalInvoice->invoice_number,
                'amount_due' => $this->finalInvoice->amount_due,
                'amount_paid' => $this->finalInvoice->amount_paid,
                'status' => $this->finalInvoice->status->value,
                'status_label' => $this->finalInvoice->status->label(),
                'created_at' => $this->finalInvoice->created_at->toDateTimeString(),
            ] : null,
            'is_deposit_paid' => $this->hasDepositPaid(),
            'is_final_paid' => $this->finalInvoice?->status === InvoiceStatus::Paid,
        ];
    }
}
