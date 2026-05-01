<?php

namespace App\Http\Resources\Public\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\InvoiceStatus;

class BookingResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'designer_name' => $this->designer?->full_name,
            'start_at' => $this->start_at?->toDateTimeString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_price' => $this->total_price,
            'deposit_invoice_id' => $this->depositInvoice?->id,
            'final_invoice_id' => $this->finalInvoice?->id,
            'is_deposit_paid' => $this->hasDepositPaid(),
            'is_final_paid' => $this->finalInvoice?->status === InvoiceStatus::Paid,
            'is_final_open' => $this->finalInvoice?->status === InvoiceStatus::Open,
        ];
    }
}
