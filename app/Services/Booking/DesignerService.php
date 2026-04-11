<?php

namespace App\Services\Booking;

use App\Data\Booking\DesignerFilterData;
use App\Models\Booking\Designer;
use App\Models\Employee\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DesignerService
{
    public function getFiltered(DesignerFilterData $filter): LengthAwarePaginator
    {
        return Designer::query()
            ->with(['user', 'employee', 'availabilities'])
            ->when($filter->search, fn ($q) => $q->where(function ($q) use ($filter) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$filter->search}%"))
                    ->orWhereHas('employee', fn ($e) => $e->where('full_name', 'ilike', "%{$filter->search}%"));
            }))
            ->when($filter->is_active !== null, fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Designer
    {
        return Designer::with(['user', 'employee', 'availabilities'])->findOrFail($id);
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

    public function getEmployeeOptions(): Collection
    {
        return Employee::query()
            ->whereNull('termination_date')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('designers')
                    ->whereColumn('designers.employee_id', 'employees.id');
            })
            ->with('user')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'user_id'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'full_name' => $e->full_name,
                'phone' => $e->phone,
                'email' => $e->user?->email,
            ]);
    }
}
