<?php

namespace App\Data\Public;

class ProductCardFilterData
{
    public function __construct(
        public ?string $type = null,
        public int $limit = 12,
        public ?string $category = null,
        public array $filters = [],
    ) {}

    public static function fromRequest($request): self
    {
        $reserved = ['type', 'limit', 'category'];
        $rawFilters = collect($request->query())->except($reserved)->toArray();

        // Normalize all filters to be arrays of strings
        $filters = collect($rawFilters)->map(function ($value) {
            return is_array($value) ? $value : [$value];
        })->toArray();

        return new self(
            type: $request->query('type'),
            limit: (int) $request->query('limit', 12),
            category: $request->query('category'),
            filters: $filters,
        );
    }
}
