<?php

namespace App\Services\Booking;

use App\Data\Booking\BookingFilterData;
use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService
{
    public function getFiltered(BookingFilterData $filter): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['customer', 'designer', 'service', 'acceptedBy', 'invoice'])
            ->when($filter->designer_id, fn ($q) => $q->where('designer_id', $filter->designer_id))
            ->when($filter->service_id, fn ($q) => $q->where('service_id', $filter->service_id))
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->customer_id, fn ($q) => $q->where('customer_id', $filter->customer_id))
            ->when($filter->search, fn ($q) => $q->whereHas('designer', fn ($d) => $d->where('name', 'ilike', "%{$filter->search}%")))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(BookingFilterData $filter): LengthAwarePaginator
    {
        return Booking::onlyTrashed()
            ->with(['customer', 'designer', 'service'])
            ->when($filter->search, fn ($q) => $q->whereHas('designer', fn ($d) => $d->where('name', 'ilike', "%{$filter->search}%")))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Booking
    {
        return Booking::with([
            'customer',
            'designer.user',
            'designer.employee',
            'service',
            'acceptedBy',
            'invoice',
        ])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return BookingStatus::options();
    }
}
