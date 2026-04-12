<?php

namespace App\Services\Hr;

use App\Data\Hr\DepartmentFilterData;
use App\Models\Hr\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DepartmentService
{
    public function getFiltered(DepartmentFilterData $filter): LengthAwarePaginator
    {
        return Department::query()
            ->withCount(['employees'])
            ->with(['manager'])
            ->when($filter->search, fn ($q) => $q->where('name', 'ilike', "%{$filter->search}%"))
            ->when($filter->is_active !== null, fn ($q) => $q->where('is_active', $filter->is_active))
            ->orderBy($filter->order_by ?: 'name', $filter->order_direction ?: 'asc')
            ->paginate($filter->per_page ?: 15);
    }

    public function getActiveOptions(): Collection
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Department $dept) => [
                'id' => $dept->id,
                'label' => $dept->name.' ('.$dept->code.')',
            ]);
    }
}
