<?php

namespace App\Http\Controllers\Employee\Booking;

use App\Data\Booking\DesignerFilterData;
use App\Http\Requests\Booking\StoreDesignerRequest;
use App\Http\Requests\Booking\UpdateDesignerRequest;
use App\Http\Resources\Employee\Booking\DesignerResource;
use App\Models\Booking\Designer;
use App\Services\Booking\DesignerService;
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

        return Inertia::render('employee/booking/designers/Index', [
            'designers' => Inertia::defer(fn () => DesignerResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDesignerRequest $request)
    {
        Designer::create($request->validated());

        return back()->with('success', 'Đã thêm nhà thiết kế.');
    }

    public function update(UpdateDesignerRequest $request, Designer $designer)
    {
        $designer->update($request->validated());

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
}
