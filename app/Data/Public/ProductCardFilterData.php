<?php

namespace App\Data\Public;

class ProductCardFilterData
{
    public function __construct(
        public ?string $type = null,
        public int $limit = 12,
        public ?string $category = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            type: $request->query('type'),
            limit: (int) $request->query('limit', 12),
            category: $request->query('category'),
        );
    }
}
