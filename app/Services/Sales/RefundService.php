<?php

namespace App\Services\Sales;

use App\Data\Sales\RefundFilterData;
use App\Enums\RefundStatus;
use App\Models\Sales\Refund;
use Illuminate\Pagination\LengthAwarePaginator;

class RefundService
{
    public function getFiltered(RefundFilterData $filter): LengthAwarePaginator
    {
        return Refund::query()
            ->with(['order', 'payment', 'requestedBy', 'processedBy'])
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->order_id, fn ($q) => $q->where('order_id', $filter->order_id))
            ->when($filter->search, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('order_number', 'ilike', "%{$filter->search}%")))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Refund
    {
        return Refund::with([
            'order',
            'payment',
            'requestedBy',
            'processedBy',
        ])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return RefundStatus::options();
    }
}
