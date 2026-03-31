<?php

namespace App\Http\Controllers\Employee\Product;

use App\Actions\Product\UpsertCollectionAction;
use App\Data\Product\CollectionFilterData;
use App\Http\Requests\Product\Collection\StoreCollectionRequest;
use App\Http\Requests\Product\Collection\UpdateCollectionRequest;
use App\Http\Resources\Product\Collection\EmployeeCollectionResource;
use App\Models\Product\Collection;
use App\Services\Product\CollectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController
{
    public function __construct(private CollectionService $service) {}

    public function index(Request $request): Response
    {
        $filter = CollectionFilterData::fromRequest($request);

        return Inertia::render('employee/products/collections/Index', [
            'collections' => Inertia::defer(fn () => EmployeeCollectionResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = CollectionFilterData::fromRequest($request);

        return Inertia::render('employee/products/collections/Trash', [
            'collections' => Inertia::defer(fn () => EmployeeCollectionResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreCollectionRequest $request, UpsertCollectionAction $action)
    {
        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm bộ sưu tập mới.');
    }

    public function update(UpdateCollectionRequest $request, Collection $collection, UpsertCollectionAction $action)
    {
        $action->execute($request->validated(), $collection);

        return back()->with('success', 'Đã cập nhật bộ sưu tập.');
    }

    public function destroy(Collection $collection)
    {
        if (! Auth::user()->can('collections.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }
        $collection->delete();

        return back()->with('success', 'Đã xóa bộ sưu tập.');
    }

    public function restore(Collection $collection)
    {
        if (! Auth::user()->can('collections.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $collection->restore();

        return back()->with('success', 'Đã khôi phục bộ sưu tập.');
    }

    public function forceDestroy(Collection $collection)
    {
        if (! Auth::user()->can('collections.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $collection->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn bộ sưu tập.');
    }
}
