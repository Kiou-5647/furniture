<?php

namespace App\Data\Product;

use App\Enums\ProductStatus;
use Illuminate\Http\Request;

class ProductFilterData
{
    public function __construct(
        public readonly ?string $vendor_id,
        public readonly ?string $category_id,
        public readonly ?string $collection_id,
        public readonly ?ProductStatus $status,
        public readonly ?bool $is_featured,
        public readonly ?bool $is_dropship,
        public readonly ?bool $is_new_arrival,
        public readonly ?string $search,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 15));

        return new self(
            vendor_id: $request->query('vendor_id'),
            category_id: $request->query('category_id'),
            collection_id: $request->query('collection_id'),
            status: ProductStatus::tryFrom($request->query('status')),
            is_featured: $request->has('is_featured') ? $request->boolean('is_featured') : null,
            is_dropship: $request->has('is_dropship') ? $request->boolean('is_dropship') : null,
            is_new_arrival: $request->has('is_new_arrival') ? $request->boolean('is_new_arrival') : null,
            search: $request->query('search'),
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage
        );
    }
}
