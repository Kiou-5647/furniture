<?php

namespace App\Builders\Product;

use App\Data\Product\BundleFilterData;
use Illuminate\Database\Eloquent\Builder;

class BundleBuilder extends Builder
{
    public function filterBy(BundleFilterData $filter): self
    {
        if ($filter->search) {
            $search = $filter->search;
            $this->where(
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
            );
        }

        if ($filter->is_active !== null) {
            $this->where('is_active', $filter->is_active);
        }


        if ($filter->created_from) {
            $this->whereDate('created_at', '>=', $filter->created_from);
        }
        if ($filter->created_to) {
            $this->whereDate('created_at', '<=', $filter->created_to);
        }

        return $this;
    }

    public function sortBy(string $orderBy, string $orderDir): self
    {
        $allowedSorts = ['created_at', 'name', 'discount_value'];
        $column = in_array($orderBy, $allowedSorts) ? $orderBy : 'created_at';
        $direction = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';

        return $this->orderBy($column, $direction);
    }
}
