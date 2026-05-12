<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateDesignerAction;
use App\Actions\Hr\UpdateDesignerAction;
use App\Constants\Permission;
use App\Data\Hr\DesignerFilterData;
use App\Http\Requests\Hr\StoreDesignerRequest;
use App\Http\Requests\Hr\UpdateDesignerRequest;
use App\Http\Resources\Employee\Hr\DesignerResource;
use App\Models\Hr\Designer;
use App\Services\Booking\BookingAvailabilityService;
use App\Services\Hr\DesignerService;
use App\Settings\WorkHourSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DesignerController
{
    public function __construct(
        private WorkHourSettings $settings,
        private DesignerService $service,
        private BookingAvailabilityService $checker
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Designer::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $filter = DesignerFilterData::fromRequest($request);

        return Inertia::render('employee/hr/designers/Index', [
            'workHours' => [
                'morning_start' => $this->settings->morning_start,
                'morning_end' => $this->settings->morning_end,
                'afternoon_start' => $this->settings->afternoon_start,
                'afternoon_end' => $this->settings->afternoon_end,
            ],
            'employeeOptions' => $this->service->getEmployeeOptions(),
            'designers' => DesignerResource::collection(
                $this->service->getFiltered($filter)
            ),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDesignerRequest $request, CreateDesignerAction $action)
    {
        if (!Gate::allows('create', Designer::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        try {
            $action->execute(
                $request->validated(),
                $request->file('avatar'),
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Đã thêm nhà thiết kế.');
    }

    public function update(UpdateDesignerRequest $request, Designer $designer, UpdateDesignerAction $action)
    {
        if (!Gate::allows('update', $designer)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }
        try {
            $action->execute(
                $designer,
                $request->validated(),
                $request->file('avatar'),
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Đã cập nhật nhà thiết kế.');
    }

    public function destroy(Designer $designer)
    {
        if (!Gate::allows('delete', $designer)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        if ($designer->user) {
            $designer->user->revokePermissionTo([
                Permission::BOOKING['SELECT'],
                Permission::BOOKING['CREATE'],
                Permission::BOOKING['UPDATE'],
            ]);
        }

        $designer->delete();

        return back()->with('success', 'Đã xóa nhà thiết kế.');
    }
}
