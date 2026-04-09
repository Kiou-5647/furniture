<?php

namespace App\Data\HR;

use Illuminate\Http\Request;

readonly class EmployeeFilterData
{
    public function __construct(
        public ?string $department_id = null,
        public ?bool $is_active = null,
        public ?string $search = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            department_id: $request->query('department_id'),
            is_active: $request->boolean('is_active') ? true : ($request->has('is_active') ? false : null),
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
