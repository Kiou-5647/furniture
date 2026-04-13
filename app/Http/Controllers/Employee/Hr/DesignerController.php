<?php

namespace App\Http\Controllers\Employee\Hr;

use App\Actions\Hr\CreateDesignerAction;
use App\Actions\Hr\UpdateDesignerAction;
use App\Data\Hr\DesignerFilterData;
use App\Http\Requests\Hr\StoreDesignerRequest;
use App\Http\Requests\Hr\UpdateDesignerRequest;
use App\Http\Resources\Employee\Hr\DesignerResource;
use App\Models\Hr\Designer;
use App\Services\Booking\BookingAvailabilityChecker;
use App\Services\Booking\DesignerAvailabilityService;
use App\Services\Hr\DesignerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DesignerController
{
    public function __construct(
        private DesignerService $service,
        private DesignerAvailabilityService $availabilityService,
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

    public function availabilityPage(Request $request): Response
    {
        $designers = Designer::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'display_name']);

        return Inertia::render('employee/hr/designers/Availability', [
            'designers' => $designers,
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
        $weeklySlots = $this->availabilityService->getWeeklySlots($designer);

        return response()->json([
            'weekly' => $weeklySlots,
        ]);
    }

    public function updateAvailabilitySlots(Request $request, Designer $designer)
    {
        $request->validate([
            'slots' => ['required', 'array'],
            'slots.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'slots.*.hour' => ['required', 'integer', 'min:0', 'max:23'],
            'slots.*.is_available' => ['required', 'boolean'],
        ]);

        $this->availabilityService->setWeeklySlots($designer, $request->input('slots'));

        return back()->with('success', 'Đã cập nhật lịch làm việc hàng tuần.');
    }

    public function availableSlots(Request $request, Designer $designer, BookingAvailabilityChecker $checker)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'service_id' => ['nullable', 'uuid', 'exists:design_services,id'],
        ]);

        $date = $request->input('date');

        $slots = $checker->getAvailableSlots($designer, $date);

        return response()->json([
            'date' => $date,
            'slots' => $slots,
        ]);
    }

    public function availableDates(Designer $designer)
    {
        $startDate = request()->query('start_date', today()->toDateString());
        $endDate = request()->query('end_date', today()->addDays(30)->toDateString());

        $dates = $this->availabilityService->getAvailableDates($designer, $startDate, $endDate);

        return response()->json([
            'dates' => $dates,
        ]);
    }
}
