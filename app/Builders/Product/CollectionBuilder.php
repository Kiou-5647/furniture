<?php

namespace App\Builders\Product;

use Illuminate\Database\Eloquent\Builder;

class CollectionBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('display_name', 'ilike', "%{$search}%")
                ->orWhere('slug', 'ilike', "%{$search}%");
        });
    }

    public function active(): self
    {
        return $this->where('is_active', true);
    }
}
