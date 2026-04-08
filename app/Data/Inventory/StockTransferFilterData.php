<?php

namespace App\Data\Inventory;

use Illuminate\Http\Request;

class StockTransferFilterData
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?string $from_location_id,
        public readonly ?string $to_location_id,
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
            status: $request->query('status'),
            from_location_id: $request->query('from_location_id'),
            to_location_id: $request->query('to_location_id'),
            search: $request->query('search'),
            date_from: $request->query('date_from'),
            date_to: $request->query('date_to'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage,
        );
    }
}
