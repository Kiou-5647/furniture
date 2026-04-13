<?php

namespace App\Services\Booking;

use App\Data\Booking\DesignServiceFilterData;
use App\Models\Booking\DesignService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DesignServiceService
{
    public function getFiltered(DesignServiceFilterData $filter): LengthAwarePaginator
    {
        return DesignService::query()
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->when($filter->type, fn ($q) => $q->byType($filter->type))
            ->when($filter->is_schedule_blocking !== null, fn ($q) => $filter->is_schedule_blocking ? $q->scheduleBlocking() : $q->nonScheduleBlocking())
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): DesignService
    {
        return DesignService::with('bookings')->findOrFail($id);
    }

    public function getActiveOptions(): Collection
    {
        return DesignService::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'base_price'])
            ->map(fn (DesignService $service) => [
                'value' => $service->id,
                'label' => $service->name.' — '.number_format($service->base_price, 0, ',', '.').'đ',
            ]);
    }
}
