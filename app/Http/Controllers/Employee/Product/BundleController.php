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
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BundleController
{
    public function __construct(
        private BundleService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Bundle::class)) {
            return back()->with('error', 'Bạn không có quyền xem danh sách gói sản phẩm.');
        }

        $filter = BundleFilterData::fromRequest($request);

        return Inertia::render('employee/products/bundles/Index', [
            'bundles' => Inertia::defer(fn() => BundleResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function create()
    {
        if (!Gate::allows('create', Bundle::class)) {
            return back()->with('error', 'Bạn không có quyền tạo gói sản phẩm mới.');
        }

        return Inertia::render('employee/products/bundles/CreateOrEdit', [
            'bundle' => null,
        ]);
    }

    public function edit(Bundle $bundle)
    {
        if (!Gate::allows('update', $bundle)) {
            return back()->with('error', 'Bạn không có quyền chỉnh sửa gói sản phẩm này.');
        }

        $bundle->load([
            'contents.productCard' => fn($q) => $q->with('product'),
            'contents.productCard.variants'
        ]);

        return Inertia::render('employee/products/bundles/CreateOrEdit', [
            'bundle' => new BundleResource($bundle),
        ]);
    }

    public function store(StoreBundleRequest $request, UpsertBundleAction $action)
    {
        if (!Gate::allows('create', Bundle::class)) {
            return back()->with('error', 'Bạn không có quyền tạo gói sản phẩm mới.');
        }

        try {
            $action->execute($request->validated());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã thêm gói sản phẩm mới.');
    }

    public function update(UpdateBundleRequest $request, Bundle $bundle, UpsertBundleAction $action)
    {
        if (!Gate::allows('update', $bundle)) {
            return back()->with('error', 'Bạn không có quyền cập nhật gói sản phẩm này.');
        }

        try {
            $action->execute($request->validated(), $bundle);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật gói sản phẩm.');
    }

    public function destroy(Bundle $bundle)
    {
        if (!Gate::allows('delete', $bundle)) {
            return back()->with('error', 'Bạn không có quyền xóa gói sản phẩm này.');
        }

        $bundle->delete();

        return back()->with('success', 'Đã xóa gói sản phẩm.');
    }
}
