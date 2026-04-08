<?php

namespace App\Data\Inventory;

use Illuminate\Http\Request;

class StockMovementFilterData
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?string $location_id,
        public readonly ?string $variant_id,
        public readonly ?string $search,
        public readonly ?string $date_from,
        public readonly ?string $date_to,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 15));

        return new self(
            type: $request->query('type'),
            location_id: $request->query('location_id'),
            variant_id: $request->query('variant_id'),
            search: $request->query('search'),
            date_from: $request->query('date_from'),
            date_to: $request->query('date_to'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage,
        );
    }
}
