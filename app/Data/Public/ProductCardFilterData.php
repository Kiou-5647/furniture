<?php

namespace App\Data\Public;

use App\Enums\ProductSortType;

class ProductCardFilterData
{
    public function __construct(
        public ?ProductSortType $type = null,
        public int $limit = 24,
        public int $page = 1,
        public ?float $min_price = null,
        public ?float $max_price = null,
        public array $filters = [],
    ) {}

    public static function fromRequest($request): self
    {
        $reserved = ['type', 'limit', 'page', 'per_page', 'min_price', 'max_price'];
        $rawFilters = collect($request->query())->except($reserved)->toArray();

        // Normalize all filters to be arrays of strings
        $filters = collect($rawFilters)->map(function ($value) {
            return is_array($value) ? $value : [$value];
        })->toArray();

        $limit = (int) $request->query('limit', $request->query('per_page', $request->cookie('per_page', 24)));

        return new self(
            type: ProductSortType::tryFrom($request->query('type')),
            limit: $limit,
            page: (int) $request->query('page', 1),
            min_price: $request->filled('min_price') ? (float) $request->query('min_price') : null,
            max_price: $request->filled('max_price') ? (float) $request->query('max_price') : null,
            filters: $filters,
        );
    }
}
