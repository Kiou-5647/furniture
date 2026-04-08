<?php

namespace App\Data\Setting;

use Illuminate\Http\Request;

class LookupNamespaceFilterData
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?bool $is_active,
        public readonly ?bool $for_variants,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 15));

        return new self(
            search: $request->query('search'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            for_variants: $request->has('for_variants') ? $request->boolean('for_variants') : null,
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage
        );
    }
}
