<?php

namespace App\Data\Inventory;

use Illuminate\Http\Request;

class InventoryFilterData
{
    public function __construct(
        public readonly ?string $location_id,
        public readonly ?string $variant_id,
        public readonly ?string $search,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly int $per_page,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            location_id: $request->query('location_id'),
            variant_id: $request->query('variant_id'),
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) $request->query('per_page', $request->cookie('per_page', 15)),
        );
    }
}
