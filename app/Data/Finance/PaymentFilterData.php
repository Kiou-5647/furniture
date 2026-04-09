<?php

namespace App\Data\Finance;

use App\Enums\PaymentStatus;
use Illuminate\Http\Request;

readonly class PaymentFilterData
{
    public function __construct(
        public ?PaymentStatus $status = null,
        public ?string $customer_id = null,
        public ?string $gateway = null,
        public ?string $search = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status') ? PaymentStatus::tryFrom($request->query('status')) : null,
            customer_id: $request->query('customer_id'),
            gateway: $request->query('gateway'),
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
