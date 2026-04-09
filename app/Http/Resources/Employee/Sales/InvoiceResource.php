<?php

namespace App\Http\Resources\Employee\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoiceable_type' => $this->invoiceable_type,
            'invoiceable_id' => $this->invoiceable_id,
            'invoiceable' => $this->resolveInvoiceable(),
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'amount_due' => $this->amount_due,
            'amount_paid' => $this->amount_paid,
            'remaining_balance' => $this->remainingBalance(),
            'validated_by' => $this->whenLoaded('validatedBy', fn () => $this->validatedBy->full_name),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }

    protected function resolveInvoiceable(): ?array
    {
        if (! $this->relationLoaded('invoiceable') || ! $this->invoiceable) {
            return null;
        }

        return [
            'id' => $this->invoiceable->id,
            'type' => class_basename($this->invoiceable),
            'number' => $this->invoiceable->order_number ?? $this->invoiceable->id,
        ];
    }
}
