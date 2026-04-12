<?php

namespace App\Services\Fulfillment;

use App\Data\Fulfillment\ShippingMethodFilterData;
use App\Models\Fulfillment\ShippingMethod;
use Illuminate\Pagination\LengthAwarePaginator;

class ShippingMethodService
{
    public function getFiltered(ShippingMethodFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['name', 'code', 'price', 'estimated_delivery_days', 'created_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'name';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return ShippingMethod::query()
            ->withCount('shipments')
            ->when($filter->is_active !== null, fn ($q) => $q->where('is_active', $filter->is_active))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(ShippingMethodFilterData $filter): LengthAwarePaginator
    {
        return ShippingMethod::onlyTrashed()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): ShippingMethod
    {
        return ShippingMethod::withCount('shipments')->findOrFail($id);
    }

    public function getActiveOptions(): array
    {
        return ShippingMethod::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'estimated_delivery_days'])
            ->map(fn ($method) => [
                'id' => $method->id,
                'label' => $method->name,
                'price' => $method->price,
                'estimated_delivery_days' => $method->estimated_delivery_days,
            ])
            ->toArray();
    }
}
