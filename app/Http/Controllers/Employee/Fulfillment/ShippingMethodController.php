<?php

namespace App\Http\Controllers\Employee\Fulfillment;

use App\Actions\Fulfillment\UpsertShippingMethodAction;
use App\Data\Fulfillment\CreateShippingMethodData;
use App\Data\Fulfillment\ShippingMethodFilterData;
use App\Http\Requests\Fulfillment\StoreShippingMethodRequest;
use App\Http\Requests\Fulfillment\UpdateShippingMethodRequest;
use App\Http\Resources\Employee\Fulfillment\ShippingMethodResource;
use App\Models\Fulfillment\ShippingMethod;
use App\Services\Fulfillment\ShippingMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ShippingMethodController
{
    public function __construct(
        private ShippingMethodService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', ShippingMethod::class)) {
            return back()->with('error', 'Bạn không có quyền xem danh sách phương thức vận chuyển.');
        }

        $filter = ShippingMethodFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipping-methods/Index', [
            'shippingMethods' => Inertia::defer(fn() => ShippingMethodResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreShippingMethodRequest $request, UpsertShippingMethodAction $action)
    {
        if (!Gate::allows('create', ShippingMethod::class)) {
            return back()->with('error', 'Bạn không có quyền tạo phương thức vận chuyển.');
        }

        $data = CreateShippingMethodData::fromRequest($request);
        try {
            $action->execute($data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã thêm phương thức vận chuyển.');
    }

    public function update(UpdateShippingMethodRequest $request, ShippingMethod $shippingMethod, UpsertShippingMethodAction $action)
    {
        if (!Gate::allows('update', $shippingMethod)) {
            return back()->with('error', 'Bạn không có quyền cập nhật phương thức vận chuyển này.');
        }

        $data = CreateShippingMethodData::fromRequest($request);
        try {
            $action->execute($data, $shippingMethod);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật phương thức vận chuyển.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        if (!Gate::allows('delete', $shippingMethod)) {
            return back()->with('error', 'Bạn không có quyền xóa phương thức vận chuyển này.');
        }

        $shippingMethod->delete();

        return back()->with('success', 'Đã xóa phương thức vận chuyển.');
    }
}
