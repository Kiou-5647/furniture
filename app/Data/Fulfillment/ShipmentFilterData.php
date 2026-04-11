<?php

namespace App\Data\Fulfillment;

use App\Enums\ShipmentStatus;
use Illuminate\Http\Request;

readonly class ShipmentFilterData
{
    public function __construct(
        public ?string $order_id = null,
        public ?ShipmentStatus $status = null,
        public ?string $search = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            order_id: $request->query('order_id'),
            status: $request->query('status') ? ShipmentStatus::tryFrom($request->query('status')) : null,
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
