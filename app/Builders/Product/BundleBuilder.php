<?php

namespace App\Builders\Product;

use Illuminate\Database\Eloquent\Builder;

class BundleBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function search(string $search): self
    {
        return $this->where('name', 'ilike', "%{$search}%");
    }

    public function withValidProducts(): self
    {
        return $this->whereHas('contents', function ($query) {
            $query->whereHas('product', function ($q) {
                $q->whereNull('deleted_at');
            });
        });
    }
}
