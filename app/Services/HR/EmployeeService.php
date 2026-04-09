<?php

namespace App\Services\HR;

use App\Data\HR\EmployeeFilterData;
use App\Models\Employee\Department;
use App\Models\Employee\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployeeService
{
    public function getFiltered(EmployeeFilterData $filter): LengthAwarePaginator
    {
        return Employee::query()
            ->with(['user', 'department'])
            ->when($filter->department_id, fn ($q) => $q->where('department_id', $filter->department_id))
            ->when($filter->is_active !== null, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('is_active', $filter->is_active)))
            ->when($filter->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$filter->search}%")))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Employee
    {
        return Employee::with(['user.roles', 'user.permissions', 'department.manager'])
            ->findOrFail($id);
    }

    public function getDepartmentOptions(): Collection
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Department $dept) => [
                'id' => $dept->id,
                'label' => $dept->name,
            ]);
    }

    public function getRoleOptions(): Collection
    {
        return Role::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($role) => [
                'id' => $role->name,
                'label' => ucfirst(str_replace('_', ' ', $role->name)),
            ]);
    }

    public function getPermissionOptions(): Collection
    {
        return Permission::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($perm) => [
                'id' => $perm->name,
                'label' => ucfirst(str_replace('_', ' ', $perm->name)),
            ]);
    }
}
