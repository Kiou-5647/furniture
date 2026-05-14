<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateEmployeeAction;
use App\Actions\Hr\RestoreEmployeeAction;
use App\Actions\Hr\TerminateEmployeeAction;
use App\Actions\Hr\UpdateEmployeeAction;
use App\Constants\Permission;
use App\Data\Hr\CreateEmployeeData;
use App\Data\Hr\EmployeeFilterData;
use App\Http\Requests\Hr\CreateEmployeeRequest;
use App\Http\Requests\Hr\UpdateEmployeeRequest;
use App\Http\Resources\Employee\Hr\EmployeeResource;
use App\Models\Hr\Employee;
use App\Services\Hr\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EmployeeController
{
    public function __construct(
        private EmployeeService $service,
    ) {}

    public function index(Request $request)
    {
        if (! Gate::allows('viewAny', Employee::class)) {
            return back()->with('error', 'Bạn không có quyền xem danh sách nhân viên.');
        }

        $filter = EmployeeFilterData::fromRequest($request);

        return Inertia::render('employee/hr/employees/Index', [
            'departmentOptions' => $this->service->getDepartmentOptions(),
            'roleOptions' => $this->service->getRoleOptions(),
            'permissionOptions' => $this->service->getPermissionOptions(),
            'storeLocationOptions' => $this->service->getStoreLocationOptions(),
            'warehouseLocationOptions' => $this->service->getWarehouseLocationOptions(),
            'rolePermissions' => $this->service->getRolePermissionsMap(),
            'employees' => Inertia::defer(fn() => EmployeeResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function access(Employee $employee)
    {
        if (! Gate::allows('grant', $employee)) {
            return back()->with('error', 'Bạn không có quyền quản lý quyền hạn cho nhân viên này.');
        }

        return Inertia::render('employee/hr/employees/Access', [
            'employeeId' => $employee->id,
        ]);
    }

    public function store(CreateEmployeeRequest $request, CreateEmployeeAction $action)
    {
        if (! Gate::allows('create', Employee::class)) {
            return back()->with('error', 'Bạn không có quyền cập nhật thông tin nhân viên này.');
        }

        $data = CreateEmployeeData::fromRequest($request);
        $roles = $request->input('roles', []);
        $permissions = $request->input('permissions', []);

        try {
            $action->execute($data, $roles, $permissions);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã tạo tài khoản nhân viên.');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployeeAction $action)
    {
        if (! Gate::allows('update', $employee)) {
            return back()->with('error', 'Bạn không có quyền cập nhật thông tin nhân viên này.');
        }

        try {
            $employee = $action->execute($employee, $request->validated());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật nhân viên.');
    }

    public function destroy(Employee $employee)
    {
        if (! Gate::allows('delete', $employee)) {
            return back()->with('error', 'Bạn không có quyền xóa nhân viên này.');
        }

        $employee->user->delete();

        return back()->with('success', 'Đã xóa nhân viên.');
    }

    public function terminate(Employee $employee, Request $request, TerminateEmployeeAction $action)
    {
        if (! Gate::allows('delete', $employee)) {
            return back()->with('error', 'Bạn không có quyền chấm dứt hợp đồng nhân viên này.');
        }

        $terminationDate = $request->input('termination_date') ? now() : null;

        try {
            $action->execute($employee, $terminationDate);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã chấm dứt nhân viên.');
    }

    public function restore(Employee $employee, RestoreEmployeeAction $action)
    {
        if (! Gate::allows('update', $employee)) {
            return back()->with('error', 'Bạn không có quyền khôi phục nhân viên này.');
        }

        try {
            $action->execute($employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã khôi phục nhân viên.');
    }

    public function permissions(Request $request, Employee $employee)
    {
        $employeeModel = $this->service->getById($employee->id, $request->user());
        $user = $employeeModel->user()->with('roles')->first();

        // Nhóm các quyền từ class Permission hằng số
        $groupedPermissions = [];
        $reflection = new \ReflectionClass(Permission::class);
        $constants = $reflection->getConstants();

        foreach ($constants as $module => $permissions) {
            if (! is_array($permissions)) {
                continue;
            }

            // Ánh xạ key của module sang nhãn tiếng Việt cho tiêu đề nhóm
            $moduleLabels = [
                'ORDER' => 'Đơn hàng',
                'BOOKING' => 'Lịch thiết kế',
                'SHIPMENT' => 'Đơn vận chuyển',
                'CATEGORY' => 'Danh mục',
                'COLLECTION' => 'Bộ sưu tập',
                'PRODUCT' => 'Sản phẩm',
                'BUNDLE' => 'Gói sản phẩm',
                'DEPARTMENT' => 'Phòng ban',
                'DESIGNER' => 'Nhà thiết kế',
                'DISCOUNT' => 'Khuyến mãi',
                'EMPLOYEE' => 'Nhân viên',
                'INVOICE' => 'Hóa đơn',
                'LOOKUP' => 'Tra cứu',
                'PAYMENT' => 'Thanh toán',
                'LOCATION' => 'Vị trí',
                'REFUND' => 'Hoàn tiền',
                'SHIPPING_METHOD' => 'Phương thức vận chuyển',
                'STOCK' => 'Tồn kho',
                'CUSTOMER' => 'Khách hàng',
                'VENDOR' => 'Nhà cung cấp',
                'SETTING' => 'Cấu hình hệ thống',
                'PERMISSION' => 'Quyền hạn',
            ];

            $groupedPermissions[] = [
                'module' => $module,
                'label' => $moduleLabels[$module] ?? $module,
                'permissions' => collect($permissions)->map(function ($label, $key) use ($employeeModel) {
                    // Sử dụng trực tiếp $label (ví dụ: 'Xem đơn hàng') vì DB lưu nhãn tiếng Việt
                    return [
                        'name' => $label,
                        'label' => $label,
                        'assigned' => $employeeModel->user->hasDirectPermission($label),
                    ];
                })->values()->toArray(),
            ];
        }

        return response()->json([
            'data' => [
                'full_name' => $employeeModel->full_name,
                'user' => [
                    'roles' => $user->roles->map(fn($role) => ['name' => $role->name])->values(),
                ],
            ],
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $this->service->getRolePermissionsMap(),
        ]);
    }

    public function syncRoles(Employee $employee, Request $request)
    {
        if (! Gate::allows('grant', $employee)) {
            return back()->with('error', 'Bạn không có quyền quản lý quyền hạn cho nhân viên này.');
        }

        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ]);

        $roles = $request->input('roles', []);
        $employee->user->syncRoles($roles);

        return response()->json(['message' => 'Đã cập nhật vai trò thành công.']);
    }

    public function syncPermissions(Employee $employee, Request $request)
    {
        if (! Gate::allows('grant', $employee)) {
            return back()->with('error', 'Bạn không có quyền quản lý quyền hạn cho nhân viên này.');
        }

        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $permissions = $request->input('permissions', []);
        $employee->user->syncPermissions($permissions);

        return response()->json(['message' => 'Đã cập nhật quyền hạn thành công.']);
    }

    public function syncRolesPermissions(Employee $employee, Request $request)
    {
        if (! Gate::allows('grant', $employee)) {
            return back()->with('error', 'Bạn không có quyền quản lý quyền hạn cho nhân viên này.');
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
