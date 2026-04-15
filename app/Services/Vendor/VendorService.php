<?php

namespace App\Services\Vendor;

use App\Data\Vendor\VendorFilterData;
use App\Models\Vendor\Vendor;
use App\Support\CacheKeys;
use App\Support\CacheTag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class VendorService
{
    public function getFiltered(VendorFilterData $filter): LengthAwarePaginator
    {
        return Cache::tags([CacheTag::VendorsList->value])
            ->remember(
                CacheKeys::getFiltersKeys('vendors_list', $filter),
                CacheKeys::TTL,
                function () use ($filter) {
                    $query = Vendor::query();

                    if ($filter->search) {
                        $query->where(function ($q) use ($filter) {
                            $q->where('name', 'like', "%{$filter->search}%")
                                ->orWhere('email', 'like', "%{$filter->search}%")
                                ->orWhere('phone', 'like', "%{$filter->search}%");
                        });
                    }

                    if ($filter->is_active !== null) {
                        $query->where('is_active', $filter->is_active);
                    }

                    return $query->orderBy($filter->order_by, $filter->order_direction)
                        ->paginate($filter->per_page);
                }
            );
    }
}
