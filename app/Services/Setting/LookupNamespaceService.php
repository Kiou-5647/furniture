<?php

namespace App\Services\Setting;

use App\Data\Setting\LookupNamespaceFilterData;
use App\Models\Setting\LookupNamespace;
use Illuminate\Pagination\LengthAwarePaginator;

class LookupNamespaceService
{
    public function getFiltered(LookupNamespaceFilterData $filter): LengthAwarePaginator
    {
        return LookupNamespace::query()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->when(! is_null($filter->for_variants), fn ($q) => $q->where('for_variants', $filter->for_variants))
            ->when(
                $filter->order_by === 'is_system' || is_null($filter->order_by),
                fn ($q) => $q->orderBy('is_system', 'desc')->orderBy('created_at', 'asc'),
                fn ($q) => $q->orderBy($filter->order_by, $filter->order_direction ?? 'desc')
            )
            ->paginate($filter->per_page ?? 15);
    }
}
