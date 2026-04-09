<?php

namespace App\Http\Resources\Employee\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchasable_type' => $this->purchasable_type,
            'purchasable_id' => $this->purchasable_id,
            'purchasable_name' => $this->purchasable?->name ?? '—',
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => (float) $this->unit_price * $this->quantity,
            'configuration' => $this->configuration,
        ];
    }
}
