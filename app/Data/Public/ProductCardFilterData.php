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
        // Extract all query parameters that aren't reserved keywords as filters
        $reserved = ['type', 'limit', 'category'];
        $filters = collect($request->query())->except($reserved)->toArray();

        return new self(
            type: $request->query('type'),
            limit: (int) $request->query('limit', 12),
            category: $request->query('category'),
            filters: $filters,
        );
    }
}
