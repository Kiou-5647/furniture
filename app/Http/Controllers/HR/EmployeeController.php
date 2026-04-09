<?php

namespace App\Http\Controllers\HR;

use App\Actions\HR\AssignEmployeeRoleAction;
use App\Actions\HR\CreateEmployeeAction;
use App\Actions\HR\GrantEmployeePermissionAction;
use App\Actions\HR\RestoreEmployeeAction;
use App\Actions\HR\RevokeEmployeePermissionAction;
use App\Actions\HR\TerminateEmployeeAction;
use App\Actions\HR\UpdateEmployeeAction;
use App\Data\HR\CreateEmployeeData;
use App\Data\HR\EmployeeFilterData;
use App\Http\Requests\HR\CreateEmployeeRequest;
use App\Http\Requests\HR\UpdateEmployeeRequest;
use App\Http\Resources\HR\EmployeeResource;
use App\Models\Employee\Employee;
use App\Services\HR\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController
{
    public function __construct(
        private EmployeeService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = EmployeeFilterData::fromRequest($request);

        return Inertia::render('hr/employees/Index', [
            'departmentOptions' => $this->service->getDepartmentOptions(),
            'employees' => Inertia::defer(fn () => EmployeeResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Employee $employee): Response
    {
        $employee = $this->service->getById($employee->id);

        return Inertia::render('hr/employees/Show', [
            'employee' => new EmployeeResource($employee),
        ]);
    }

    public function store(CreateEmployeeRequest $request, CreateEmployeeAction $action)
    {
        $data = CreateEmployeeData::fromRequest($request);
        $employee = $action->execute($data);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Đã tạo tài khoản nhân viên.');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployeeAction $action)
    {
        $employee = $action->execute($employee, $request->validated());

        return back()->with('success', 'Đã cập nhật nhân viên.');
    }

    public function terminate(Employee $employee, Request $request, TerminateEmployeeAction $action)
    {
        $terminationDate = $request->input('termination_date') ? now() : null;
        $action->execute($employee, $terminationDate);

        return back()->with('success', 'Đã chấm dứt nhân viên.');
    }

    public function restore(Employee $employee, RestoreEmployeeAction $action)
    {
        $action->execute($employee);

        return back()->with('success', 'Đã khôi phục nhân viên.');
    }

    public function permissions(Employee $employee)
    {
        return new EmployeeResource($this->service->getById($employee->id));
    }

    public function assignRole(Employee $employee, Request $request, AssignEmployeeRoleAction $action)
    {
        $request->validate(['role' => ['required', 'string']]);

        $action->execute($employee->user, $request->input('role'));

        return back()->with('success', 'Đã gán vai trò.');
    }

    public function removeRole(Employee $employee, string $role)
    {
        if (! Auth::user()->can('hr.roles.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $employee->user->removeRole($role);

        return back()->with('success', 'Đã gỡ vai trò.');
    }

    public function grantPermission(Employee $employee, string $permission, GrantEmployeePermissionAction $action)
    {
        $action->execute($employee->user, $permission);

        return back()->with('success', 'Đã cấp quyền.');
    }

    public function revokePermission(Employee $employee, string $permission, RevokeEmployeePermissionAction $action)
    {
        $action->execute($employee->user, $permission);

        return back()->with('success', 'Đã thu hồi quyền.');
    }
}
