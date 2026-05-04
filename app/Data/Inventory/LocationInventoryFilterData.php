<?php

namespace App\Data\Inventory;

use Illuminate\Http\Request;

class LocationInventoryFilterData
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 12));

        return new self(
            search: $request->query('search'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage
        );
    }
}
