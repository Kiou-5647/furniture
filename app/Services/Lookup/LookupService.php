<?php

namespace App\Services\Lookup;

use App\Builders\Setting\LookupBuilder;
use App\Data\LookupFilterData;
use App\Models\Setting\Lookup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LookupService
{
    public function getNamespaces(): Collection
    {
        return Lookup::query()
            ->select('namespace')
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('namespace')
            ->orderBy('namespace')
            ->get();
    }

    public function getByNamespace(LookupFilterData $filter): LengthAwarePaginator
    {
        return Lookup::query()
            ->when($filter->namespace, fn (LookupBuilder $q) => $q->byNamespace($filter->namespace))
            ->when($filter->search, fn (LookupBuilder $q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'key', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }
}
