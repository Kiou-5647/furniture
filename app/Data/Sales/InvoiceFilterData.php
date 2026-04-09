<?php

namespace App\Data\Sales;

use App\Enums\InvoiceStatus;
use Illuminate\Http\Request;

readonly class InvoiceFilterData
{
    public function __construct(
        public ?InvoiceStatus $status = null,
        public ?string $type = null,
        public ?string $invoiceable_type = null,
        public ?string $search = null,
        public string $order_by = 'created_at',
        public string $order_direction = 'desc',
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status') ? InvoiceStatus::tryFrom($request->query('status')) : null,
            type: $request->query('type'),
            invoiceable_type: $request->query('invoiceable_type'),
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? $request->cookie('per_page', 15)),
        );
    }
}
