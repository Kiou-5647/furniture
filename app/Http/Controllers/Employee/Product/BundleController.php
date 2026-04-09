<?php

namespace App\Http\Controllers\Employee\Product;

use App\Actions\Product\UpsertBundleAction;
use App\Data\Product\BundleFilterData;
use App\Http\Requests\Product\Bundle\StoreBundleRequest;
use App\Http\Requests\Product\Bundle\UpdateBundleRequest;
use App\Http\Resources\Employee\Product\BundleResource;
use App\Models\Product\Bundle;
use App\Services\Product\BundleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BundleController
{
    public function __construct(
        private BundleService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = BundleFilterData::fromRequest($request);

        return Inertia::render('employee/products/bundles/Index', [
            'discountTypeOptions' => $this->service->getDiscountTypeOptions(),
            'bundles' => Inertia::defer(fn () => BundleResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = BundleFilterData::fromRequest($request);

        return Inertia::render('employee/products/bundles/Trash', [
            'bundles' => Inertia::defer(fn () => BundleResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Bundle $bundle): Response
    {
        $bundle = $this->service->getById($bundle->id);

        return Inertia::render('employee/products/bundles/Show', [
            'bundle' => new BundleResource($bundle),
        ]);
    }

    public function store(StoreBundleRequest $request, UpsertBundleAction $action)
    {
        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm gói sản phẩm mới.');
    }

    public function update(UpdateBundleRequest $request, Bundle $bundle, UpsertBundleAction $action)
    {
        $action->execute($request->validated(), $bundle);

        return back()->with('success', 'Đã cập nhật gói sản phẩm.');
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();

        return back()->with('success', 'Đã xóa gói sản phẩm.');
    }

    public function restore(Bundle $bundle)
    {
        if (! Auth::user()->can('bundles.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $bundle->restore();

        return back()->with('success', 'Đã khôi phục gói sản phẩm.');
    }

    public function forceDestroy(Bundle $bundle)
    {
        if (! Auth::user()->can('bundles.manage')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $bundle->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn gói sản phẩm.');
    }
}
