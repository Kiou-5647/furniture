<?php

namespace App\Http\Controllers\Employee\Booking;

use App\Data\Booking\DesignServiceFilterData;
use App\Enums\DesignServiceType;
use App\Http\Requests\Booking\StoreDesignServiceRequest;
use App\Http\Requests\Booking\UpdateDesignServiceRequest;
use App\Http\Resources\Employee\Booking\DesignServiceResource;
use App\Models\Booking\DesignService;
use App\Services\Booking\DesignServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DesignServiceController
{
    public function __construct(
        private DesignServiceService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = DesignServiceFilterData::fromRequest($request);

        return Inertia::render('employee/booking/services/Index', [
            'typeOptions' => DesignServiceType::options(),
            'services' => Inertia::defer(fn () => DesignServiceResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDesignServiceRequest $request)
    {
        DesignService::create($request->validated());

        return back()->with('success', 'Đã thêm dịch vụ thiết kế.');
    }

    public function update(UpdateDesignServiceRequest $request, DesignService $service)
    {
        $service->update($request->validated());

        return back()->with('success', 'Đã cập nhật dịch vụ thiết kế.');
    }

    public function destroy(DesignService $service)
    {
        if (! Auth::user()->can('design_services.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $service->delete();

        return back()->with('success', 'Đã xóa dịch vụ thiết kế.');
    }

    public function restore(DesignService $service)
    {
        if (! Auth::user()->can('design_services.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $service->restore();

        return back()->with('success', 'Đã khôi phục dịch vụ thiết kế.');
    }
}
