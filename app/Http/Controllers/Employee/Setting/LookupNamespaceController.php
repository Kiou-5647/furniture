<?php

namespace App\Http\Controllers\Employee\Setting;

use App\Data\Setting\LookupNamespaceFilterData;
use App\Http\Requests\Setting\LookupNamespace\StoreLookupNamespaceRequest;
use App\Http\Requests\Setting\LookupNamespace\UpdateLookupNamespaceRequest;
use App\Http\Resources\Employee\Setting\LookupNamespaceResource;
use App\Models\Setting\LookupNamespace;
use App\Services\Setting\LookupNamespaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LookupNamespaceController
{
    public function __construct(private LookupNamespaceService $service) {}

    public function index(Request $request): Response
    {
        if (!Gate::allows('viewAny', LookupNamespace::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập danh mục tra cứu!');
        }

        $filter = LookupNamespaceFilterData::fromRequest($request);

        return Inertia::render('employee/settings/lookup-namespaces/Index', [
            'namespaces' => Inertia::defer(fn () => LookupNamespaceResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => [
                'search' => $filter->search,
                'is_active' => $filter->is_active,
                'for_variants' => $filter->for_variants,
                'order_by' => $filter->order_by,
                'order_direction' => $filter->order_direction,
            ],
            'canCreate' => Gate::allows('create', LookupNamespace::class),
        ]);
    }

    public function store(StoreLookupNamespaceRequest $request)
    {
        if (!Gate::allows('create', LookupNamespace::class)) {
            return back()->with('error', 'Bạn không có quyền tạo danh mục tra cứu mới!');
        }

        LookupNamespace::create($request->validated());

        return back()->with('success', 'Đã thêm danh mục tra cứu mới.');
    }

    public function update(UpdateLookupNamespaceRequest $request, LookupNamespace $lookupNamespace)
    {
        if (!Gate::allows('update', $lookupNamespace)) {
            return back()->with('error', 'Bạn không có quyền cập nhật danh mục tra cứu này!');
        }

        $lookupNamespace->update($request->validated());

        return back()->with('success', 'Đã cập nhật danh mục tra cứu.');
    }

    public function destroy(LookupNamespace $lookupNamespace)
    {
        if (!Gate::allows('delete', $lookupNamespace)) {
            return back()->with('error', 'Bạn không có quyền xóa danh mục tra cứu này!');
        }

        if ($lookupNamespace->is_system) {
            return back()->with('error', 'Không thể xóa danh mục hệ thống!');
        }

        $lookupNamespace->delete();

        return back()->with('success', 'Đã xóa danh mục tra cứu.');
    }
}
