<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateEmployeeAction;
use App\Actions\Hr\RestoreEmployeeAction;
use App\Actions\Hr\TerminateEmployeeAction;
use App\Actions\Hr\UpdateEmployeeAction;
use App\Data\Hr\CreateEmployeeData;
use App\Data\Hr\EmployeeFilterData;
use App\Http\Requests\Hr\CreateEmployeeRequest;
use App\Http\Requests\Hr\UpdateEmployeeRequest;
use App\Http\Resources\Employee\Hr\EmployeeResource;
use App\Models\Hr\Employee;
use App\Services\Hr\EmployeeService;
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

        return Inertia::render('employee/hr/employees/Index', [
            'departmentOptions' => $this->service->getDepartmentOptions(),
            'roleOptions' => $this->service->getRoleOptions(),
            'permissionOptions' => $this->service->getPermissionOptions(),
            'locationOptions' => $this->service->getLocationOptions(),
            'rolePermissions' => $this->service->getRolePermissionsMap(),
            'employees' => Inertia::defer(fn () => EmployeeResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Employee $employee): Response
    {
        $employee = $this->service->getById($employee->id);

        return Inertia::render('employee/hr/employees/Show', [
            'employee' => new EmployeeResource($employee),
        ]);
    }

    public function store(CreateEmployeeRequest $request, CreateEmployeeAction $action)
    {
        $data = CreateEmployeeData::fromRequest($request);
        $roles = $request->input('roles', []);
        $permissions = $request->input('permissions', []);
        $action->execute($data, $roles, $permissions);

        return back()->with('success', 'Đã tạo tài khoản nhân viên.');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployeeAction $action)
    {
        $roles = $request->input('roles', []);
        $permissions = $request->input('permissions', []);
        $employee = $action->execute($employee, $request->validated(), $roles, $permissions);

        return back()->with('success', 'Đã cập nhật nhân viên.');
    }

    public function destroy(Employee $employee)
    {
        if (! Auth::user()->can('employees.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $employee->user->delete();

        return back()->with('success', 'Đã xóa nhân viên.');
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
        $resource = new EmployeeResource($this->service->getById($employee->id));

        return response()->json([
            'data' => $resource->toArray(request()),
            'rolePermissions' => $this->service->getRolePermissionsMap(),
        ]);
    }

    public function syncRolesPermissions(Employee $employee, Request $request)
    {
        if (! Auth::user()->can('roles/manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $roles = $request->input('roles', []);
        $permissions = $request->input('permissions', []);

        $employee->user->syncRoles($roles);
        $employee->user->syncPermissions($permissions);

        return back()->with('success', 'Đã cập nhật quyền hạn.');
    }
}
