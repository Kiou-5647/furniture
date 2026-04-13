<?php

namespace App\Services\Booking;

use App\Data\Booking\BookingFilterData;
use App\Enums\BookingStatus;
use App\Models\Auth\User;
use App\Models\Booking\Booking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BookingService
{
    public function getCustomerOptions(): Collection
    {
        return User::query()
            ->where('type', 'customer')
            ->where('is_active', true)
            ->with('customer')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->customer?->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->customer?->phone,
            ]);
    }

    public function getFiltered(BookingFilterData $filter): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['customer', 'designer', 'service', 'acceptedBy', 'depositInvoice', 'finalInvoice', 'sessions'])
            ->when($filter->designer_id, fn ($q) => $q->byDesigner($filter->designer_id))
            ->when($filter->service_id, fn ($q) => $q->byService($filter->service_id))
            ->when($filter->status, fn ($q) => $q->byStatus($filter->status))
            ->when($filter->customer_id, fn ($q) => $q->byCustomer($filter->customer_id))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(BookingFilterData $filter): LengthAwarePaginator
    {
        return Booking::onlyTrashed()
            ->with(['customer', 'designer', 'service'])
            ->when($filter->search, fn ($q) => $q->search($filter->search))
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
            'depositInvoice',
            'finalInvoice',
            'sessions',
        ])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return BookingStatus::options();
    }
}
