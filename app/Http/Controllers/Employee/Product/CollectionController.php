<?php

namespace App\Http\Controllers\Employee\Product;

use App\Actions\Product\UpsertCollectionAction;
use App\Data\Product\CollectionFilterData;
use App\Http\Requests\Product\Collection\StoreCollectionRequest;
use App\Http\Requests\Product\Collection\UpdateCollectionRequest;
use App\Http\Resources\Employee\Product\CollectionResource;
use App\Models\Product\Collection;
use App\Services\Product\CollectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController
{
    public function __construct(private CollectionService $service) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Collection::class)) {
            return back()->with('error', 'Bạn không có quyền xem bộ sưu tập.');
        }

        $filter = CollectionFilterData::fromRequest($request);

        return Inertia::render('employee/products/collections/Index', [
            'collections' => Inertia::defer(fn() => CollectionResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreCollectionRequest $request, UpsertCollectionAction $action)
    {
        if (!Gate::allows('create', Collection::class)) {
            return back()->with('error', 'Bạn không có quyền tạo bộ sưu tập.');
        }

        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm bộ sưu tập mới.');
    }

    public function update(UpdateCollectionRequest $request, Collection $collection, UpsertCollectionAction $action)
    {
        if (!Gate::allows('update', $collection)) {
            return back()->with('error', 'Bạn không có quyền sửa bộ sưu tập.');
        }

        $action->execute($request->validated(), $collection);

        return back()->with('success', 'Đã cập nhật bộ sưu tập.');
    }

    public function destroy(Collection $collection)
    {
        if (!Gate::allows('delete', $collection)) {
            return back()->with('error', 'Bạn không có quyền xóa bộ sưu tập.');
        }

        $collection->delete();

        return back()->with('success', 'Đã xóa bộ sưu tập.');
    }
}
