<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\UpsertDiscountAction;
use App\Data\Sales\DiscountFilterData;
use App\Http\Requests\Sales\StoreDiscountRequest;
use App\Http\Requests\Sales\UpdateDiscountRequest;
use App\Http\Resources\Employee\Sales\DiscountResource;
use App\Models\Sales\Discount;
use App\Services\Sales\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DiscountController
{
    public function __construct(private DiscountService $service) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Discount::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $filter = DiscountFilterData::fromRequest($request);

        return Inertia::render('employee/sales/discounts/Index', [
            'discounts' => Inertia::defer(fn() => DiscountResource::collection(
                $this->service->getFiltered($filter)
            )),
            'discountableTypes' => $this->service->getDiscountableTypes(),
            'filters' => $filter,
        ]);
    }

    public function store(StoreDiscountRequest $request, UpsertDiscountAction $action)
    {
        if (!Gate::allows('create', Discount::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $action->execute($request->validated());

        return back();
    }

    public function update(UpdateDiscountRequest $request, Discount $discount, UpsertDiscountAction $action)
    {
        if (!Gate::allows('update', $discount)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $action->execute($request->validated(), $discount);

        return back();
    }

    public function destroy(Discount $discount)
    {
        if (!Gate::allows('delete', $discount)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $discount->delete();

        return back();
    }

    public function getProducts(Request $request): JsonResponse
    {
        return response()->json($this->service->getTargetProducts($request->query('search', '')));
    }

    public function getVariants(Request $request): JsonResponse
    {
        return response()->json($this->service->getTargetVariants($request->query('search', '')));
    }

    public function getCategories(Request $request): JsonResponse
    {
        return response()->json($this->service->getTargetCategories($request->query('search', '')));
    }

    public function getCollections(Request $request): JsonResponse
    {
        return response()->json($this->service->getTargetCollections($request->query('search', '')));
    }

    public function getVendors(Request $request): JsonResponse
    {
        return response()->json($this->service->getTargetVendors($request->query('search', '')));
    }
}
