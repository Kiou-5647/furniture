<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Data\Inventory\LocationFilterData;
use App\Data\Inventory\LocationInventoryFilterData;
use App\Http\Requests\Inventory\StoreLocationRequest;
use App\Http\Requests\Inventory\UpdateLocationRequest;
use App\Http\Resources\Employee\Inventory\LocationInventoryResource;
use App\Http\Resources\Employee\Inventory\LocationResource;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Services\Inventory\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LocationController
{
    public function __construct(
        private LocationService $service,
        private \App\Actions\Inventory\BulkImportStockAction $bulkImportStock,
    ) {}

    public function index(Request $request): Response
    {
        $filter = LocationFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/locations/Index', [
            'typeOptions' => $this->service->getTypeOptions(),
            'managerOptions' => $this->service->getManagerOptions()->toArray(),
            'locations' => Inertia::defer(fn() => LocationResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreLocationRequest $request)
    {
        Gate::authorize('create', Location::class);

        $this->service->create($request->validated());

        return back()->with('success', 'Đã thêm vị trí mới.');
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        Gate::authorize('manage', $location);

        $this->service->update($location, $request->validated());

        return back()->with('success', 'Đã cập nhật vị trí.');
    }

    public function destroy(Location $location)
    {
        Gate::authorize('manage', $location);

        if ($location->inventories()->exists()) {
            return back()->with('error', 'Không thể xóa vị trí đang có tồn kho!');
        }

        $location->delete();

        return back()->with('success', 'Đã xóa vị trí.');
    }

    public function show(Request $request, Location $location): Response
    {
        $filter = LocationInventoryFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/locations/Show', [
            'location' => new LocationResource($location),
            'variantsList' => \App\Models\Product\ProductVariant::with('product')->get(),
            'inventory' => LocationInventoryResource::collection(
                $this->service->getInventoryByLocation($location, $filter)
            ),
            'stats' => $this->service->getLocationStats($location),
            'filters' => $filter,
        ]);
    }

    public function bulkImport(\App\Http\Requests\Inventory\BulkImportStockRequest $request, Location $location)
    {
        try {
            $this->bulkImportStock->handle(
                $location,
                Auth::user()?->employee,
                $request->validated()['items']
            );

            return back()->with('success', 'Nhập kho thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function export(Location $location)
    {
        $fileName = "bao-cao-ton-kho-{$location->code}-" . now()->format('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Inventory\LocationInventoryExport($location), 
            $fileName
        );
    }
}
