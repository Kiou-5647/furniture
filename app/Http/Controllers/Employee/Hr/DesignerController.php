<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateDesignerAction;
use App\Actions\Hr\UpdateDesignerAction;
use App\Data\Hr\DesignerFilterData;
use App\Http\Requests\Hr\StoreDesignerRequest;
use App\Http\Requests\Hr\UpdateDesignerAvailabilitiesRequest;
use App\Http\Requests\Hr\UpdateDesignerRequest;
use App\Http\Resources\Employee\Hr\DesignerResource;
use App\Models\Hr\Designer;
use App\Models\Hr\DesignerAvailability;
use App\Services\Hr\DesignerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DesignerController
{
    public function __construct(
        private DesignerService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = DesignerFilterData::fromRequest($request);

        return Inertia::render('employee/hr/designers/Index', [
            'employeeOptions' => $this->service->getEmployeeOptions(),
            'designers' => Inertia::defer(fn () => DesignerResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDesignerRequest $request, CreateDesignerAction $action)
    {
        $action->execute(
            $request->validated(),
            $request->file('avatar'),
        );

        return back()->with('success', 'Đã thêm nhà thiết kế.');
    }

    public function update(UpdateDesignerRequest $request, Designer $designer, UpdateDesignerAction $action)
    {
        $action->execute(
            $designer,
            $request->validated(),
            $request->file('avatar'),
        );

        return back()->with('success', 'Đã cập nhật nhà thiết kế.');
    }

    public function destroy(Designer $designer)
    {
        if (! Auth::user()->can('designers.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $designer->delete();

        return back()->with('success', 'Đã xóa nhà thiết kế.');
    }

    public function restore(Designer $designer)
    {
        if (! Auth::user()->can('designers.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $designer->restore();

        return back()->with('success', 'Đã khôi phục nhà thiết kế.');
    }

    public function availabilities(Designer $designer)
    {
        $availabilities = DesignerAvailability::where('designer_id', $designer->id)
            ->orderBy('day_of_week')
            ->get(['id', 'day_of_week', 'start_time', 'end_time']);

        return response()->json([
            'data' => $availabilities->mapWithKeys(fn ($a) => [
                $a->day_of_week => [
                    'id' => $a->id,
                    'start_time' => $a->start_time,
                    'end_time' => $a->end_time,
                ],
            ]),
        ]);
    }

    public function updateAvailabilities(UpdateDesignerAvailabilitiesRequest $request, Designer $designer)
    {
        $designer->availabilities()->delete();

        foreach ($request->input('availabilities', []) as $slot) {
            DesignerAvailability::create([
                'designer_id' => $designer->id,
                'day_of_week' => $slot['day_of_week'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }

        return back()->with('success', 'Đã cập nhật lịch làm việc.');
    }
}
