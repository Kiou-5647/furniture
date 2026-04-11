<?php

namespace App\Services\Fulfillment;

use App\Data\Fulfillment\ShipmentFilterData;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ShipmentService
{
    public function getFiltered(ShipmentFilterData $filter): LengthAwarePaginator
    {
        return Shipment::query()
            ->with(['order', 'originLocation', 'shippingMethod', 'handledBy', 'items.sourceLocation'])
            ->when($filter->order_id, fn ($q) => $q->where('order_id', $filter->order_id))
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->search, fn ($q) => $q->where('shipment_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(ShipmentFilterData $filter): LengthAwarePaginator
    {
        return Shipment::onlyTrashed()
            ->with(['order'])
            ->when($filter->search, fn ($q) => $q->where('shipment_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Shipment
    {
        return Shipment::with([
            'order',
            'originLocation',
            'shippingMethod',
            'handledBy',
            'items.orderItem',
            'items.sourceLocation',
        ])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return ShipmentStatus::options();
    }

    public function getCarrierOptions(): Collection
    {
        return collect([
            ['id' => 'GHN', 'label' => 'Giao Hàng Nhanh'],
            ['id' => 'GHTK', 'label' => 'Giao Hàng Tiết Kiệm'],
            ['id' => 'VIETTEL', 'label' => 'ViettelPost'],
            ['id' => 'VNPOST', 'label' => 'VNPost'],
            ['id' => 'GRAB', 'label' => 'GrabExpress'],
            ['id' => 'OTHER', 'label' => 'Khác'],
        ]);
    }
}
