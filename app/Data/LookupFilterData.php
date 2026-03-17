<?php

namespace App\Data;

use Illuminate\Http\Request;

class LookupFilterData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?string $namespace,
        public readonly ?string $search,
        public readonly bool $systemOnly,
        public readonly bool $customOnly,
        public readonly ?string $orderBy,
        public readonly ?string $orderDirection,
        public readonly ?int $perPage
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            namespace: $request->query('namespace'),
            search: $request->query('search'),
            systemOnly: $request->boolean('system_only'),
            customOnly: $request->boolean('custom_only'),
            orderBy: $request->query('order_by', 'key'),
            orderDirection: $request->query('order_direction', 'asc'),
            perPage: (int) $request->query('perPage', 15)
        );
    }
}
