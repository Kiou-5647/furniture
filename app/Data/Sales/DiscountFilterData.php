<?php

namespace App\Data\Sales;

use Illuminate\Http\Request;

class DiscountFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?string $type = null,
        public ?string $discountable_type = null,
        public ?string $discountable_id = null,
        public ?bool $is_active = null,
        public ?string $start_after = null,
        public ?string $end_before = null,
        public ?string $order_by = null,
        public ?string $order_direction = null,
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search'),
            type: $request->query('type'),
            discountable_type: $request->query('discountable_type'),
            discountable_id: $request->query('discountable_id'),
            is_active: $request->has('is_active')
                ? $request->boolean('is_active')
                : null,
            start_after: $request->query('start_after'),
            end_before: $request->query('end_before'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
