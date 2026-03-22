<?php

namespace App\Services\Setting;

use App\Builders\Setting\LookupBuilder;
use App\Data\Setting\LookupFilterData;
use App\Enums\LookupType;
use App\Models\Setting\Lookup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LookupService
{
    public function getNamespaces(): Collection
    {
        $databaseCounts = Lookup::query()
            ->select('namespace')
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('namespace')
            ->pluck('count', 'namespace')
            ->toArray();

        return collect(LookupType::cases())->map(function (LookupType $type) use ($databaseCounts) {
            return [
                'namespace' => $type->value,
                'label' => $type->label(),
                'count' => $databaseCounts[$type->value] ?? 0,
            ];
        });
    }

    public function getByNamespace(LookupFilterData $filter): LengthAwarePaginator
    {
        return Lookup::query()
            ->when($filter->namespace, fn (LookupBuilder $q) => $q->byNamespace($filter->namespace))
            ->when($filter->search, fn (LookupBuilder $q) => $q->search($filter->search))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by ?? 'slug', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }
}
