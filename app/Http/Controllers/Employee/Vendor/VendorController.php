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

class VendorController
{
    public function __construct(protected VendorService $service) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Vendor::class)) {
            return back()->with('error', 'Bạn không có quyền xem danh sách nhà cung cấp.');
        }

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
        if (!Gate::allows('create', Vendor::class)) {
            return back()->with('error', 'Bạn không có quyền tạo nhà cung cấp.');
        }

        $data = CreateVendorData::fromRequest($request);
        try {
            $action->execute($data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã thêm nhà cung cấp mới.');
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor, UpsertVendorAction $action)
    {
        if (!Gate::allows('update', $vendor)) {
            return back()->with('error', 'Bạn không có quyền cập nhật nhà cung cấp này.');
        }

        $data = CreateVendorData::fromRequest($request);
        try {
            $action->execute($data, $vendor);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật nhà cung cấp.');
    }

    public function destroy(Vendor $vendor)
    {
        if (!Gate::allows('delete', $vendor)) {
            return back()->with('error', 'Bạn không có quyền xóa nhà cung cấp này.');
        }

        $vendor->delete();

        return back()->with('success', 'Đã xóa nhà cung cấp.');
    }
}
