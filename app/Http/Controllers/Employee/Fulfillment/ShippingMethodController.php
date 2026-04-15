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
use Inertia\Response;

class ShippingMethodController
{
    public function __construct(
        private ShippingMethodService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = ShippingMethodFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipping-methods/Index', [
            'shippingMethods' => Inertia::defer(fn () => ShippingMethodResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = ShippingMethodFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipping-methods/Trash', [
            'shippingMethods' => Inertia::defer(fn () => ShippingMethodResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreShippingMethodRequest $request, UpsertShippingMethodAction $action)
    {
        Gate::authorize('create', ShippingMethod::class);

        $data = CreateShippingMethodData::fromRequest($request);
        $action->execute($data);

        return back()->with('success', 'Đã thêm phương thức vận chuyển.');
    }

    public function update(UpdateShippingMethodRequest $request, ShippingMethod $shippingMethod, UpsertShippingMethodAction $action)
    {
        Gate::authorize('manage', $shippingMethod);

        $data = CreateShippingMethodData::fromRequest($request);
        $action->execute($data, $shippingMethod);

        return back()->with('success', 'Đã cập nhật phương thức vận chuyển.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        Gate::authorize('manage', $shippingMethod);

        $shippingMethod->delete();

        return back()->with('success', 'Đã xóa phương thức vận chuyển.');
    }

    public function restore(ShippingMethod $shippingMethod)
    {
        Gate::authorize('manage', $shippingMethod);

        $shippingMethod->restore();

        return back()->with('success', 'Đã khôi phục phương thức vận chuyển.');
    }

    public function forceDestroy(ShippingMethod $shippingMethod)
    {
        Gate::authorize('manage', $shippingMethod);

        $shippingMethod->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn phương thức vận chuyển.');
    }
}
