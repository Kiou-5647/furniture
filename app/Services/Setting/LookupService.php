<?php

namespace App\Services\Setting;

use App\Builders\Setting\LookupBuilder;
use App\Data\Setting\LookupFilterData;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Support\CacheKeys;
use App\Support\CacheTag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LookupService
{
    public function getNamespaces(): Collection
    {
        return Cache::tags([CacheTag::LookupNamespaces->value])
            ->remember(CacheTag::LookupNamespaces->key('data'), CacheKeys::TTL, fn () => $this->buildNamespaces());
    }

    protected function buildNamespaces(): Collection
    {
        $counts = Lookup::query()
            ->select('namespace_id')
            ->selectRaw('COUNT(*) AS count')
            ->whereNotNull('namespace_id')
            ->groupBy('namespace_id')
            ->pluck('count', 'namespace_id')
            ->toArray();

        $nullCount = Lookup::query()->whereNull('namespace_id')->count();
        $totalCount = Lookup::query()->count();

        $namespaces = LookupNamespace::query()
            ->orderBy('is_system', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($ns) => [
                'id' => $ns->id,
                'slug' => $ns->slug,
                'label' => $ns->display_name,
                'for_variants' => $ns->for_variants,
                'is_system' => $ns->is_system,
                'count' => $counts[$ns->id] ?? 0,
            ])->values();

        $namespaces->prepend([
            'id' => null,
            'slug' => '_all',
            'label' => 'Tất cả tra cứu',
            'for_variants' => false,
            'is_system' => false,
            'count' => $totalCount,
        ]);

        if ($nullCount > 0) {
            $namespaces->prepend([
                'id' => null,
                'slug' => '_null',
                'label' => 'Không có danh mục',
                'for_variants' => false,
                'count' => $nullCount,
            ]);
        }

        return $namespaces;
    }

    public function getFiltered(LookupFilterData $filter): LengthAwarePaginator
    {
        return Lookup::query()
            ->with('namespace')
            ->when($filter->namespace, fn (LookupBuilder $q) => $q->byNamespace($filter->namespace))
            ->when($filter->search, fn (LookupBuilder $q) => $q->search($filter->search))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by ?? 'slug', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(LookupFilterData $filter): LengthAwarePaginator
    {
        return Lookup::onlyTrashed()
            ->with('namespace')
            ->when($filter->namespace, fn ($q) => $q->byNamespace($filter->namespace))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getFilterableNamespaces(): Collection
    {
        return Cache::tags([CacheTag::FilterableNamespaces->value])
            ->remember(CacheTag::FilterableNamespaces->key('data'), CacheKeys::TTL, fn () => LookupNamespace::query()
                ->where('is_filterable', true)
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'slug', 'display_name'])
                ->map(fn ($ns) => [
                    'id' => $ns->id,
                    'slug' => $ns->slug,
                    'label' => $ns->display_name,
                ])->values());
    }
}
