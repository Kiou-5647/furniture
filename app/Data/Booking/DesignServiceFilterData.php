<?php

namespace App\Data\Booking;

use Illuminate\Http\Request;

readonly class DesignServiceFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?string $type = null,
        public ?bool $is_schedule_blocking = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search'),
            type: $request->query('type'),
            is_schedule_blocking: $request->boolean('is_schedule_blocking') ? true : ($request->has('is_schedule_blocking') ? false : null),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
