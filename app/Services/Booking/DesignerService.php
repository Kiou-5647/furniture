<?php

namespace App\Services\Booking;

use App\Data\Booking\DesignerFilterData;
use App\Models\Booking\Designer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DesignerService
{
    public function getFiltered(DesignerFilterData $filter): LengthAwarePaginator
    {
        return Designer::query()
            ->with(['user', 'employee', 'vendorUser'])
            ->when($filter->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$filter->search}%")))
            ->when($filter->is_active !== null, fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Designer
    {
        return Designer::with(['user', 'employee', 'vendorUser', 'availabilities'])->findOrFail($id);
    }

    public function getActiveOptions(): Collection
    {
        return Designer::query()
            ->where('is_active', true)
            ->orderBy('hourly_rate')
            ->get(['id', 'hourly_rate'])
            ->map(fn (Designer $designer) => [
                'id' => $designer->id,
                'label' => $designer->display_name.' — '.number_format($designer->hourly_rate, 0, ',', '.').'đ/h',
            ]);
    }
}
