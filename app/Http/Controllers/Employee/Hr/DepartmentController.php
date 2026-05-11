<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateDepartmentAction;
use App\Actions\Hr\UpdateDepartmentAction;
use App\Data\Hr\DepartmentFilterData;
use App\Http\Requests\Hr\StoreDepartmentRequest;
use App\Http\Requests\Hr\UpdateDepartmentRequest;
use App\Http\Resources\Employee\Hr\DepartmentResource;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Services\Hr\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController
{
    public function __construct(
        private DepartmentService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Department::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $filter = DepartmentFilterData::fromRequest($request);

        return Inertia::render('employee/hr/departments/Index', [
            'departments' => Inertia::defer(fn() => DepartmentResource::collection(
                $this->service->getFiltered($filter)
            )),
            'managerOptions' => $this->getManagerOptions(),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDepartmentRequest $request, CreateDepartmentAction $action)
    {
        if (!Gate::allows('create', Department::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $action->execute($request->validated());

        return back()->with('success', 'Đã tạo phòng ban.');
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartmentAction $action)
    {
        if (!Gate::allows('update', $department)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $action->execute($department, $request->validated());

        return back()->with('success', 'Đã cập nhật phòng ban.');
    }

    public function destroy(Department $department)
    {
        if (!Gate::allows('delete', $department)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $department->delete();

        return back()->with('success', 'Đã xóa phòng ban.');
    }

    protected function getManagerOptions(): array
    {
        return Employee::query()
            ->with('user')
            ->whereNull('termination_date')
            ->get()
            ->map(fn($emp) => [
                'id' => $emp->id,
                'label' => $emp->full_name,
            ])->toArray();
    }
}
