<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateDesignerAction;
use App\Actions\Hr\UpdateDesignerAction;
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
use Inertia\Response;

class DesignerController
{
    public function __construct(
        private WorkHourSettings $settings,
        private DesignerService $service,
        private BookingAvailabilityService $checker
    ) {}

    public function index(Request $request): Response
    {
        $filter = DesignerFilterData::fromRequest($request);

        return Inertia::render('employee/hr/designers/Index', [
            'workHours' => [
                'morning_start' => $this->settings->morning_start,
                'morning_end' => $this->settings->morning_end,
                'afternoon_start' => $this->settings->afternoon_start,
                'afternoon_end' => $this->settings->afternoon_end,
            ],
            'employeeOptions' => $this->service->getEmployeeOptions(),
            'designers' => Inertia::defer(fn() => DesignerResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDesignerRequest $request, CreateDesignerAction $action)
    {
        Gate::authorize('create', Designer::class);
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
        Gate::authorize('manage', $designer);

        $action->execute(
            $designer,
            $request->validated(),
            $request->file('avatar'),
        );

        return back()->with('success', 'Đã cập nhật nhà thiết kế.');
    }

    public function destroy(Designer $designer)
    {
        Gate::authorize('manage', $designer);

        $designer->delete();

        return back()->with('success', 'Đã xóa nhà thiết kế.');
    }

    public function restore(Designer $designer)
    {
        Gate::authorize('manage', $designer);

        $designer->restore();

        return back()->with('success', 'Đã khôi phục nhà thiết kế.');
    }

    public function availabilities(Designer $designer)
    {
        $weeklySlots = $this->service->getWeeklySlots($designer);

        return response()->json([
            'weekly' => $weeklySlots,
        ]);
    }

    public function availableSlots(Request $request, Designer $designer)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);


        $slots = $this->checker->getAvailableSlotsForDate($designer, $request->input('date'));

        $slotsMap = [];
        for ($hour = 5; $hour <= 23; $hour++) {
            $slotsMap[$hour] = in_array($hour, $slots) ? 1 : 0;
        }

        return response()->json([
            'date' => $request->input('date'),
            'slots' => $slotsMap,
        ]);
    }

    public function availableDates(Designer $designer)
    {
        $startDate = request()->query('start_date', today()->toDateString());
        $endDate = request()->query('end_date', today()->addDays(30)->toDateString());

        $dates = $this->checker->getAvailableDates($designer, $startDate, $endDate);

        return response()->json([
            'dates' => $dates,
        ]);
    }
}
