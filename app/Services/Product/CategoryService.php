<?php

namespace App\Services\Product;

use App\Data\Product\CategoryFilterData;
use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Support\CacheKeys;
use App\Support\CacheTag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public function getCategoryGroups(): Collection
    {
        return Cache::tags([CacheTag::CategoryGroups->value])
            ->remember(CacheTag::CategoryGroups->key('data'), CacheKeys::TTL, fn() => $this->buildCategoryGroups());
    }

    protected function buildCategoryGroups(): Collection
    {
        $counts = Category::query()
            ->select('group_id')
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('group_id')
            ->pluck('count', 'group_id');

        $ns = LookupNamespace::where('slug', 'nhom-danh-muc')->first();
        if (! $ns) {
            return collect();
        }

        return $ns->activeLookups()
            ->get()
            ->map(fn(Lookup $group) => [
                'id' => $group->id,
                'slug' => $group->slug,
                'label' => $group->display_name,
                'count' => $counts[$group->id] ?? 0,
            ]);
    }

    public function getRoomOptions(): Collection
    {
        return Cache::tags([CacheTag::CategoryRooms->value])
            ->remember(
                CacheTag::CategoryRooms->key('data'),
                CacheKeys::TTL,
                fn() => LookupNamespace::where('slug', 'phong')->first()?->activeLookups()->get() ?? collect()
            );
    }

    public function getFilterableSpecOptions(): Collection
    {
        return Cache::tags([CacheTag::LookupNamespaces->value])
            ->remember(
                CacheTag::LookupNamespaces->key('data'),
                CacheKeys::TTL,
                fn() => LookupNamespace::whereNot('slug', 'nhom-danh-muc')->select(['id', 'display_name'])->get() ?? collect()
            );
    }

    public function getFiltered(CategoryFilterData $filter): LengthAwarePaginator
    {
        return Category::query()
            ->with(['group', 'rooms', 'filterableSpecs'])
            ->when($filter->group_id, fn($q) => $q->where('group_id', $filter->group_id))
            ->when($filter->product_type, fn($q) => $q->byProductType($filter->product_type))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->when($filter->room_ids, fn($q) => $q->inRooms($filter->room_ids))
            ->when($filter->namespace_ids, fn($q) => $q->inNamespaces($filter->namespace_ids))
            ->when(! is_null($filter->is_active), fn($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by ?? 'display_name', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(CategoryFilterData $filter): LengthAwarePaginator
    {
        return Category::onlyTrashed()
            ->with(['group', 'rooms', 'filterableSpecs'])
            ->when($filter->group_id, fn($q) => $q->where('group_id', $filter->group_id))
            ->when($filter->product_type, fn($q) => $q->byProductType($filter->product_type))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->when($filter->room_ids, fn($q) => $q->inRooms($filter->room_ids))
            ->when($filter->namespace_ids, fn($q) => $q->inNamespaces($filter->namespace_ids))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }
}
