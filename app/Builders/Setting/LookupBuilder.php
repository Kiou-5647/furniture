<?php

namespace App\Builders\Setting;

use Illuminate\Database\Eloquent\Builder;

class LookupBuilder extends Builder
{
    public function byNamespace(string $namespaceSlug): self
    {
        if ($namespaceSlug === '_null') {
            return $this->whereNull('namespace_id');
        }

        return $this->whereHas('namespace', fn ($q) => $q->where('slug', $namespaceSlug));
    }

    public function byNamespaceId(string $namespaceId): self
    {
        return $this->where('namespace_id', $namespaceId);
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('display_name', 'ilike', "%{$search}%")
                ->orWhere('slug', 'ilike', "%{$search}%");
        });
    }
}
