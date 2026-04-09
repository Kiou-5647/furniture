<?php

namespace App\Http\Resources\Employee\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transfer_number' => $this->transfer_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'from_location' => $this->whenLoaded('fromLocation', fn () => [
                'id' => $this->fromLocation->id,
                'code' => $this->fromLocation->code,
                'name' => $this->fromLocation->name,
                'type' => $this->fromLocation->type?->value,
            ]),
            'to_location' => $this->whenLoaded('toLocation', fn () => [
                'id' => $this->toLocation->id,
                'code' => $this->toLocation->code,
                'name' => $this->toLocation->name,
                'type' => $this->toLocation->type?->value,
            ]),
            'requested_by' => $this->whenLoaded('requestedBy', fn () => [
                'id' => $this->requestedBy->id,
                'full_name' => $this->requestedBy->full_name,
            ]),
            'received_by' => $this->whenLoaded('receivedBy', fn () => [
                'id' => $this->receivedBy?->id,
                'full_name' => $this->receivedBy?->full_name,
            ]),
            'items' => StockTransferItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items', $this->items_count),
            'notes' => $this->notes,
            'received_at' => $this->received_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
