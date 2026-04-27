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
use Inertia\Response;

class DiscountController
{
    public function __construct(private DiscountService $service) {}

    public function index(Request $request): Response
    {
        Gate::authorize('discounts.view');

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
        Gate::authorize('createDiscount', Discount::class);

        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm giảm giá mới.');
    }

    public function update(UpdateDiscountRequest $request, Discount $discount, UpsertDiscountAction $action)
    {
        Gate::authorize('manageDiscount', $discount);

        $action->execute($request->validated(), $discount);

        return back()->with('success', 'Đã cập nhật giảm giá.');
    }

    public function destroy(Discount $discount)
    {
        Gate::authorize('manageDiscount', $discount);

        $discount->delete();

        return back()->with('success', 'Đã xóa giảm giá.');
    }

    public function getCategories(): JsonResponse
    {
        return response()->json(
            \App\Models\Product\Category::all()->map(fn($item) => [
                'label' => $item->display_name ?? $item->name,
                'value' => $item->id,
            ])
        );
    }

    public function getCollections(): JsonResponse
    {
        return response()->json(
            \App\Models\Product\Collection::all()->map(fn($item) => [
                'label' => $item->display_name ?? $item->name,
                'value' => $item->id,
            ])
        );
    }

    public function getVendors(): JsonResponse
    {
        return response()->json(
            \App\Models\Vendor\Vendor::all()->map(fn($item) => [
                'label' => $item->name,
                'value' => $item->id,
            ])
        );
    }
}
