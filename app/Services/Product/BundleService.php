<?php

namespace App\Services\Product;

use App\Data\Product\BundleFilterData;
use App\Models\Product\Bundle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BundleService
{
    public function getFiltered(BundleFilterData $filter): LengthAwarePaginator
    {
        $paginator = Bundle::query()
            ->with('contents.productCard')
            ->filterBy($filter)
            ->sortBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);

        $paginator->getCollection()->transform(function ($bundle) {
            $bundle->is_available = $this->isAvailable($bundle);
            return $bundle;
        });

        return $paginator;
    }

    public function getTrashedFiltered(BundleFilterData $filter): LengthAwarePaginator
    {
        $paginator = Bundle::onlyTrashed()
            ->with('contents.productCard')
            ->filterBy($filter)
            ->sortBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);

        $paginator->getCollection()->transform(function ($bundle) {
            $bundle->is_available = $this->isAvailable($bundle);
            return $bundle;
        });

        return $paginator;
    }

    public function isAvailable(Bundle $bundle, ?string $locationId = null): bool
    {
        if ($bundle->contents->isEmpty()) {
            return false;
        }

        foreach ($bundle->contents as $content) {
            $card = $content->productCard;
            if (!$card) return false;

            $hasStock = $card->variants()
                ->whereHas('inventories', function ($q) use ($locationId) {
                    $q->where('quantity', '>', 0);
                    if ($locationId) {
                        $q->where('location_id', $locationId);
                    }
                })->exists();

            if (!$hasStock) return false;
        }

        return true;
    }
}
