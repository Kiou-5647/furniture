<?php

namespace App\Http\Resources\Public\Customer;

use App\Services\Customer\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'totals' => $this->whenLoaded('items', fn () => $this->calculateTotals()),
            'item_count' => $this->whenLoaded('items', fn () => $this->items->sum('quantity')),
        ];
    }

    protected function calculateTotals(): array
    {
        $cartService = app(CartService::class);
        $totals = $cartService->calculateTotals($this->resource);

        return [
            'item_count' => $totals->item_count,
            'subtotal' => $totals->subtotal,
            'discount' => $totals->discount,
            'total' => $totals->total,
        ];
    }
}
