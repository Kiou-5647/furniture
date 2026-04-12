<?php

namespace App\Data\Hr;

use Illuminate\Http\Request;

class DepartmentFilterData
{
    public function __construct(
        public ?bool $is_active = null,
        public ?string $search = null,
        public ?string $order_by = null,
        public ?string $order_direction = null,
        public ?int $per_page = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 15));

        return new self(
            search: $request->query('search'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage
        );
    }
}
