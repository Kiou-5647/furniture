<?php

namespace App\Data;

use App\Enums\LookupType;
use Illuminate\Http\Request;

class LookupFilterData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?LookupType $namespace,
        public readonly ?string $search,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            namespace: LookupType::tryFrom($request->query('namespace')) ?? LookupType::Colors,
            search: $request->query('search'),
            order_by: $request->query('order_by', 'key'),
            order_direction: $request->query('order_direction', 'asc'),
            per_page: (int) $request->query('per_page', 15)
        );
    }
}
