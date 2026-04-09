<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Data\Inventory\LocationFilterData;
use App\Http\Requests\Inventory\StoreLocationRequest;
use App\Http\Requests\Inventory\UpdateLocationRequest;
use App\Http\Resources\Employee\Inventory\LocationResource;
use App\Models\Inventory\Location;
use App\Services\Inventory\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LocationController
{
    public function __construct(
        private LocationService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = LocationFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/locations/Index', [
            'typeOptions' => $this->service->getTypeOptions(),
            'managerOptions' => $this->service->getManagerOptions()->toArray(),
            'locations' => Inertia::defer(fn () => LocationResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = LocationFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/locations/Trash', [
            'typeOptions' => $this->service->getTypeOptions(),
            'managerOptions' => $this->service->getManagerOptions()->toArray(),
            'locations' => Inertia::defer(fn () => LocationResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreLocationRequest $request)
    {
        $this->service->create($request->validated());

        return back()->with('success', 'Đã thêm vị trí mới.');
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        $this->service->update($location, $request->validated());

        return back()->with('success', 'Đã cập nhật vị trí.');
    }

    public function restore(Location $location)
    {
        if (! Auth::user()->can('inventory.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $location->restore();

        return back()->with('success', 'Đã khôi phục vị trí.');
    }

    public function destroy(Location $location)
    {
        if (! Auth::user()->can('inventory.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        if ($location->inventories()->exists()) {
            return back()->with('error', 'Không thể xóa vị trí đang có tồn kho!');
        }

        $location->delete();

        return back()->with('success', 'Đã xóa vị trí.');
    }

    public function forceDestroy(Location $location)
    {
        if (! Auth::user()->can('inventory.manage')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $location->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn vị trí.');
    }
}
