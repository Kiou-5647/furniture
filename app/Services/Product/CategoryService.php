<?php

namespace App\Services\Product;

use App\Data\Product\CategoryFilterData;
use App\Enums\LookupType;
use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryService
{
    public function getCategoryGroups(): Collection
    {
        $counts = Category::query()
            ->select('group_id')
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('group_id')
            ->pluck('count', 'group_id');

        return Lookup::query()
            ->byNamespace(LookupType::CategoryGroup)
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
        return Lookup::query()->byNamespace(LookupType::Rooms)->get();
    }

    public function getFiltered(CategoryFilterData $filter): LengthAwarePaginator
    {
        return Category::query()
            ->with(['group', 'rooms'])
            ->when($filter->group_id, fn ($q) => $q->where('group_id', $filter->group_id))
            ->when($filter->product_type, fn ($q) => $q->byProductType($filter->product_type))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by ?? 'display_name', $filter->order_direction ?? 'asc')
            ->paginate($filter->per_page ?? 15);
    }
}
