<?php

namespace App\Data\Booking;

use App\Enums\BookingStatus;
use Illuminate\Http\Request;

readonly class BookingFilterData
{
    public function __construct(
        public ?string $designer_id = null,
        public ?string $service_id = null,
        public ?BookingStatus $status = null,
        public ?string $customer_id = null,
        public ?string $search = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            designer_id: $request->query('designer_id'),
            service_id: $request->query('service_id'),
            status: $request->query('status') ? BookingStatus::tryFrom($request->query('status')) : null,
            customer_id: $request->query('customer_id'),
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
