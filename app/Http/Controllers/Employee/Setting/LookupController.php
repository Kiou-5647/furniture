<?php

namespace App\Http\Controllers\Employee\Setting;

use App\Actions\Setting\UpsertLookupAction;
use App\Data\Setting\LookupFilterData;
use App\Http\Requests\Setting\Lookup\StoreLookupRequest;
use App\Http\Requests\Setting\Lookup\UpdateLookupRequest;
use App\Http\Resources\Employee\Setting\LookupResource;
use App\Models\Setting\Lookup;
use App\Services\Setting\LookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LookupController
{
    public function __construct(private LookupService $service) {}

    public function index(Request $request, ?string $namespace = null)
    {
        if (!Gate::allows('viewAny', Lookup::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập tra cứu!');
        }

        $filter = LookupFilterData::fromRequest($request, $namespace);

        return Inertia::render('employee/settings/lookups/Index', [
            'namespaces' => $this->service->getNamespaces(),
            'categories' => $this->service->getCategories(),
            'lookups' => Inertia::defer(fn() => LookupResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
            'canCreate' => Gate::allows('create', Lookup::class),
        ]);
    }

    public function store(StoreLookupRequest $request, UpsertLookupAction $action)
    {
        if (!Gate::allows('create', Lookup::class)) {
            return back()->with('error', 'Bạn không có quyền tạo tra cứu mới!');
        }

        try {
            $action->execute($request->validated());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã thêm tra cứu mới.');
    }

    public function update(UpdateLookupRequest $request, Lookup $lookup, UpsertLookupAction $action)
    {
        if (!Gate::allows('update', $lookup)) {
            return back()->with('error', 'Bạn không có quyền cập nhật tra cứu này!');
        }

        try {
            $action->execute($request->validated(), $lookup);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật tra cứu.');
    }

    public function destroy(Lookup $lookup)
    {
        if (!Gate::allows('delete', $lookup)) {
            return back()->with('error', 'Bạn không có quyền xóa tra cứu này!');
        }

        $lookup->delete();

        return back()->with('success', 'Đã xóa tra cứu.');
    }
}
