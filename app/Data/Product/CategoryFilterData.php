<?php

namespace App\Data\Product;

use App\Enums\ProductType;
use Illuminate\Http\Request;

class CategoryFilterData
{
    public function __construct(
        public readonly ?int $group_id,
        public readonly ?ProductType $product_type,
        public readonly ?string $search,
        public readonly ?bool $is_active,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request, ?int $groupId): self
    {
        $defaultPerPage = $request->cookie('per_page', 15);

        return new self(
            group_id: $groupId ?? ($request->has('group_id') ? (int) $request->query('group_id') : null),
            product_type: ProductType::tryFrom($request->query('product_type')),
            search: $request->query('search'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: (int) $request->query('per_page', (int) $defaultPerPage)
        );
    }
}
