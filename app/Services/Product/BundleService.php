<?php

namespace App\Services\Product;

use App\Data\Product\BundleFilterData;
use App\Enums\BundleDiscountType;
use App\Models\Product\Bundle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BundleService
{
    public function getFiltered(BundleFilterData $filter): LengthAwarePaginator
    {
        return Bundle::query()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when($filter->is_active !== null, fn ($q) => $q->where('is_active', $filter->is_active))
            ->with(['contents.product'])
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(BundleFilterData $filter): LengthAwarePaginator
    {
        return Bundle::onlyTrashed()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Bundle
    {
        return Bundle::with(['contents.product'])->findOrFail($id);
    }

    public function getActiveOptions(): Collection
    {
        return Bundle::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Bundle $bundle) => [
                'id' => $bundle->id,
                'label' => $bundle->name,
            ]);
    }

    public function getDiscountTypeOptions(): array
    {
        return BundleDiscountType::options();
    }
}
