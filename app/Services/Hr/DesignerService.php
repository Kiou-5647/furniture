<?php

namespace App\Services\Hr;

use App\Data\Hr\DesignerFilterData;
use App\Models\Hr\Employee;
use App\Models\Hr\Designer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DesignerService
{
    public function getFiltered(DesignerFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['full_name', 'hourly_rate', 'created_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return Designer::query()
            ->with(['user', 'employee.user', 'availabilities'])
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->when($filter->is_active !== null, fn($q) => $q->byActiveStatus($filter->is_active))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Designer
    {
        return Designer::with(['user', 'employee.user', 'availabilities'])->findOrFail($id);
    }

    public function getActiveOptions(): Collection
    {
        return Designer::query()
            ->where('is_active', true)
            ->orderBy('hourly_rate')
            ->get(['id', 'hourly_rate'])
            ->map(fn(Designer $designer) => [
                'id' => $designer->id,
                'label' => $designer->display_name . ' — ' . number_format($designer->hourly_rate, 0, ',', '.') . 'đ/h',
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
            ->map(fn($e) => [
                'id' => $e->id,
                'full_name' => $e->full_name,
                'phone' => $e->phone,
                'email' => $e->user?->email,
            ]);
    }
}
