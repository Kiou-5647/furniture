<?php

namespace App\Data\Vendor;

use Illuminate\Http\Request;

readonly class VendorFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?bool $is_active = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
