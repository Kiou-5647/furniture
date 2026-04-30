<?php

namespace App\Http\Resources\Public\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function array(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'paid_at' => $this->paid_at,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
