<?php

namespace App\Http\Controllers\HR;

use App\Actions\HR\CreateDepartmentAction;
use App\Actions\HR\UpdateDepartmentAction;
use App\Http\Requests\HR\StoreDepartmentRequest;
use App\Http\Requests\HR\UpdateDepartmentRequest;
use App\Http\Resources\HR\DepartmentResource;
use App\Models\Employee\Department;
use App\Models\Employee\Employee;
use App\Services\HR\DepartmentService;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController
{
    public function __construct(
        private DepartmentService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render('hr/departments/Index', [
            'departments' => Inertia::defer(fn () => DepartmentResource::collection(
                $this->service->getFiltered()
            )),
            'managerOptions' => $this->getManagerOptions(),
        ]);
    }

    public function store(StoreDepartmentRequest $request, CreateDepartmentAction $action)
    {
        $action->execute($request->validated());

        return back()->with('success', 'Đã tạo phòng ban.');
    }

    public function update(UpdateDepartmentRequest $request, $department, UpdateDepartmentAction $action)
    {
        $department = Department::findOrFail($department);
        $action->execute($department, $request->validated());

        return back()->with('success', 'Đã cập nhật phòng ban.');
    }

    public function destroy($department)
    {
        $department = Department::findOrFail($department);
        $department->delete();

        return back()->with('success', 'Đã xóa phòng ban.');
    }

    protected function getManagerOptions(): array
    {
        return Employee::query()
            ->with('user')
            ->whereNull('termination_date')
            ->get()
            ->map(fn ($emp) => [
                'id' => $emp->id,
                'label' => $emp->full_name,
            ])->toArray();
    }
}
