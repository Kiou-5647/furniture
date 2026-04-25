<?php

namespace App\Data\Product;

use Illuminate\Http\Request;

readonly class BundleFilterData
{
    public function __construct(
        public ?string $search,
        public ?bool $is_active,
        public ?string $order_by,
        public ?string $order_direction,
        public ?string $created_from,
        public ?string $created_to,
        public int $per_page = 15,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->input('search'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            created_from: $request->input('created_from'),
            created_to: $request->input('created_to'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: (int) $request->input('per_page', 15),
        );
    }
}
