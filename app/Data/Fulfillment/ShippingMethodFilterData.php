<?php

namespace App\Data\Fulfillment;

use Illuminate\Http\Request;

readonly class ShippingMethodFilterData
{
    public function __construct(
        public ?bool $is_active = null,
        public ?string $search = null,
        public string $order_by = 'name',
        public string $order_direction = 'asc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            is_active: $request->query('is_active') !== null ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN) : null,
            search: $request->query('search'),
            order_by: $request->query('order_by', 'name'),
            order_direction: $request->query('order_direction', 'asc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
