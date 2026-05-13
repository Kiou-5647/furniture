<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Actions\Inventory\BulkImportStockAction;
use App\Data\Inventory\LocationFilterData;
use App\Data\Inventory\LocationInventoryFilterData;
use App\Http\Requests\Inventory\StoreLocationRequest;
use App\Http\Requests\Inventory\UpdateLocationRequest;
use App\Http\Resources\Employee\Inventory\LocationInventoryResource;
use App\Http\Resources\Employee\Inventory\LocationResource;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Product\ProductVariant;
use App\Services\Inventory\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LocationController
{
    public function __construct(
        private LocationService $service,
        private BulkImportStockAction $bulkImportStock,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Location::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $filter = LocationFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/locations/Index', [
            'canCreate' => Gate::allows('create', Location::class),
            'typeOptions' => $this->service->getTypeOptions(),
            'managerOptions' => $this->service->getManagerOptions()->toArray(),
            'locations' => Inertia::defer(fn() => LocationResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreLocationRequest $request)
    {
        if (!Gate::allows('create', Location::class)) {
            return back()->with('error', 'Bạn không có quyền tạo vị trí mới.');
        }

        $this->service->create($request->validated());

        return back()->with('success', 'Đã thêm vị trí mới.');
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        if (!Gate::allows('update', $location)) {
            return back()->with('error', 'Bạn không có quyền cập nhật vị trí này.');
        }

        $this->service->update($location, $request->validated());

        return back()->with('success', 'Đã cập nhật vị trí.');
    }

    public function destroy(Location $location)
    {
        if (!Gate::allows('delete', $location)) {
            return back()->with('error', 'Bạn không có quyền xóa vị trí này.');
        }

        if ($location->inventories()->exists()) {
            return back()->with('error', 'Không thể xóa vị trí đang có tồn kho!');
        }

        $location->delete();

        return back()->with('success', 'Đã xóa vị trí.');
    }

    public function show(Request $request, Location $location)
    {
        if (!Gate::allows('view', $location)) {
            return back()->with('error', 'Bạn không có quyền xem chi tiết vị trí này.');
        }

        $filter = LocationInventoryFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/locations/Show', [
            'location' => new LocationResource($location),
            'variantsList' => ProductVariant::with('product')->get(),
            'inventory' => LocationInventoryResource::collection(
                $this->service->getInventoryByLocation($location, $filter)
            ),
            'stats' => $this->service->getLocationStats($location),
            'filters' => $filter,
            'canManageStock' => Gate::allows('update', StockMovement::class),
        ]);
    }

    public function bulkImport(\App\Http\Requests\Inventory\BulkImportStockRequest $request, Location $location)
    {
        if (!Gate::allows('view', $location)) {
            return back()->with('error', 'Bạn không có quyền truy cập vị trí này.');
        }

        if (!Gate::allows('update', StockMovement::class)) {
            return back()->with('error', 'Bạn không có quyền nhập dữ liệu tồn kho.');
        }

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
