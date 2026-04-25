<?php

namespace App\Services\Fulfillment;

use App\Data\Fulfillment\ShipmentFilterData;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use Illuminate\Pagination\LengthAwarePaginator;

class ShipmentService
{
    public function getFiltered(ShipmentFilterData $filter): LengthAwarePaginator
    {
        return Shipment::query()
            ->with(['order', 'originLocation', 'shippingMethod', 'handledBy', 'items'])
            ->when($filter->order_id, fn($q) => $q->where('order_id', $filter->order_id))
            ->when($filter->status, fn($q) => $q->where('status', $filter->status))
            ->when($filter->search, fn($q) => $q->where('shipment_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(ShipmentFilterData $filter): LengthAwarePaginator
    {
        return Shipment::onlyTrashed()
            ->with(['order'])
            ->when($filter->search, fn($q) => $q->where('shipment_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Shipment
    {
        return Shipment::with([
            'order.customer',
            'originLocation',
            'shippingMethod',
            'handledBy',
            'items.orderItem.purchasable',
            'items.variant',
        ])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return ShipmentStatus::options();
    }
}
