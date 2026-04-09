<?php

namespace App\Services\HR;

use App\Models\Employee\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DepartmentService
{
    public function getFiltered(): LengthAwarePaginator
    {
        return Department::query()
            ->withCount(['employees'])
            ->with(['manager'])
            ->orderBy('name')
            ->paginate(15);
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
