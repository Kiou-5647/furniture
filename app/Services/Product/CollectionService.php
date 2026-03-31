<?php

namespace App\Services\Product;

use App\Data\Product\CollectionFilterData;
use App\Models\Product\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CollectionService
{
    public function getFiltered(CollectionFilterData $filter): LengthAwarePaginator
    {
        return Collection::query()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->when(! is_null($filter->is_featured), fn ($q) => $q->where('is_featured', $filter->is_featured))
            ->orderBy($filter->order_by ?? 'display_name', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(CollectionFilterData $filter): LengthAwarePaginator
    {
        return Collection::onlyTrashed()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }
}
