<?php

namespace App\Data\Setting;

use App\Models\Setting\LookupNamespace;
use Illuminate\Http\Request;

class LookupFilterData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?string $namespace,
        public readonly ?string $search,
        public readonly ?bool $is_active,
        public readonly ?string $order_by,
        public readonly ?string $order_direction,
        public readonly ?int $per_page
    ) {}

    public static function fromRequest(Request $request, ?string $pathNamespace): self
    {
        $perPage = (int) $request->query('per_page', $request->cookie('per_page', 15));

        $nsValue = $pathNamespace ?? $request->query('namespace');

        if ($nsValue === '_null') {
            $namespace = '_null';
        } elseif ($nsValue === '_all') {
            $namespace = null;
        } elseif ($nsValue) {
            $ns = LookupNamespace::where('slug', $nsValue)->first();
            $namespace = $ns?->slug;
        } else {
            $namespace = null;
        }

        return new self(
            namespace: $namespace,
            search: $request->query('search'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            order_by: $request->query('order_by'),
            order_direction: $request->query('order_direction'),
            per_page: $perPage
        );
    }
}
