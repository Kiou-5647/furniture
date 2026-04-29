<?php

namespace App\Http\Controllers\Employee\Vendor;

use App\Actions\Vendor\UpsertVendorAction;
use App\Data\Vendor\CreateVendorData;
use App\Data\Vendor\VendorFilterData;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\Employee\Vendor\VendorResource;
use App\Models\Vendor\Vendor;
use App\Services\Vendor\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VendorController
{
    public function __construct(protected VendorService $service) {}

    public function index(Request $request): Response
    {
        Gate::authorize('view', Vendor::class);

        $filter = VendorFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/vendor/Index', [
            'vendors' => Inertia::defer(fn() => VendorResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreVendorRequest $request, UpsertVendorAction $action)
    {
        Gate::authorize('create', Vendor::class);

        $data = CreateVendorData::fromRequest($request);
        $action->execute($data);

        return back()->with('success', 'Đã thêm nhà cung cấp mới.');
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor, UpsertVendorAction $action)
    {
        Gate::authorize('manage', $vendor);

        $data = CreateVendorData::fromRequest($request);
        $action->execute($data, $vendor);

        return back()->with('success', 'Đã cập nhật nhà cung cấp.');
    }

    public function destroy(Vendor $vendor)
    {
        Gate::authorize('manage', $vendor);

        $vendor->delete();

        return back()->with('success', 'Đã xóa nhà cung cấp.');
    }
}
