<?php

namespace App\Http\Resources\Employee\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order?->order_number,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'total_amount' => $this->order->total_amount,
            ]),
            'payment' => $this->whenLoaded('payment', fn () => [
                'id' => $this->payment->id,
                'gateway' => $this->payment->gateway,
                'amount' => $this->payment->amount,
                'status' => $this->payment->status->value,
            ]),
            'amount' => $this->amount,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'requested_by' => $this->whenLoaded('requestedBy', fn () => [
                'full_name' => $this->requestedBy->full_name,
                'phone' => $this->requestedBy->phone,
            ]),
            'processed_by' => $this->whenLoaded('processedBy', fn () => [
                'full_name' => $this->processedBy->full_name,
            ]),
            'notes' => $this->notes,
            'processed_at' => $this->processed_at?->format('d/m/Y H:i'),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
