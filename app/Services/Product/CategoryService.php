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
            ->remember(CacheTag::CategoryGroups->key('data'), CacheKeys::TTL, fn () => $this->buildCategoryGroups());
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
            ->map(fn (Lookup $group) => [
                'id' => $group->id,
                'slug' => $group->slug,
                'label' => $group->display_name,
                'count' => $counts[$group->id] ?? 0,
            ]);
    }

    public function getRoomOptions(): Collection
    {
        return Cache::tags([CacheTag::CategoryRooms->value])
            ->remember(CacheTag::CategoryRooms->key('data'), CacheKeys::TTL, fn () => LookupNamespace::where('slug', 'phong')->first()?->activeLookups()->get() ?? collect());
    }

    public function getFiltered(CategoryFilterData $filter): LengthAwarePaginator
    {
        return Category::query()
            ->with(['group', 'room'])
            ->when($filter->group_id, fn ($q) => $q->where('group_id', $filter->group_id))
            ->when($filter->product_type, fn ($q) => $q->byProductType($filter->product_type))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by ?? 'display_name', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(CategoryFilterData $filter): LengthAwarePaginator
    {
        return Category::onlyTrashed()
            ->with(['group', 'room'])
            ->when($filter->group_id, fn ($q) => $q->where('group_id', $filter->group_id))
            ->when($filter->product_type, fn ($q) => $q->byProductType($filter->product_type))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getAvailableFilters(string $categorySlug): Collection
    {
        return Cache::tags([CacheTag::CategoryFilters->value])
            ->remember("{$categorySlug}", CacheKeys::TTL, fn () => $this->buildAvailableFilters($categorySlug));
    }

    protected function buildAvailableFilters(string $categorySlug): Collection
    {
        $category = Category::where('slug', $categorySlug)->first();
        if (! $category || empty($category->filterable_specs)) {
            return collect();
        }

        $filterableSlugs = $category->filterable_specs ?? [];

        $namespaces = LookupNamespace::query()
            ->whereIn('slug', $filterableSlugs)
            ->where('is_filterable', true)
            ->where('is_active', true)
            ->with(['activeLookups' => fn ($q) => $q->orderBy('display_name')])
            ->get();

        return $namespaces->map(fn ($ns) => [
            'namespace' => $ns->slug,
            'label' => $ns->display_name,
            'options' => $ns->activeLookups->map(fn ($lookup) => [
                'slug' => $lookup->slug,
                'label' => $lookup->display_name,
                'metadata' => $lookup->metadata ?? [],
                'image_url' => $lookup->getFirstMediaUrl('image', 'webp') ?: null,
            ])->values(),
        ])->values();
    }
}
