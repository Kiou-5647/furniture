<?php

namespace App\Data\Inventory;

use Illuminate\Http\Request;

class LocationFilterData
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?bool $is_active,
        public readonly ?string $search,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 15));

        return new self(
            type: $request->query('type'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            search: $request->query('search'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage
        );
    }
}
