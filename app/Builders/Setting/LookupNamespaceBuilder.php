<?php

namespace App\Builders\Setting;

use Illuminate\Database\Eloquent\Builder;

class LookupNamespaceBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('display_name', 'ilike', "%{$search}%")
                ->orWhere('slug', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
        });
    }
}
