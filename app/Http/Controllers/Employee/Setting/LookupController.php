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

    public function index(Request $request, ?string $namespace = null): Response
    {
        $filter = LookupFilterData::fromRequest($request, $namespace);

        return Inertia::render('employee/settings/lookups/Index', [
            'namespaces' => $this->service->getNamespaces(),
            'categories' => $this->service->getCategories(),
            'lookups' => Inertia::defer(fn() => LookupResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request, ?string $namespace = null): Response
    {
        $filter = LookupFilterData::fromRequest($request, $namespace);

        return Inertia::render('employee/settings/lookups/Trash', [
            'namespaces' => $this->service->getNamespaces(),
            'lookups' => Inertia::defer(fn() => LookupResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreLookupRequest $request, UpsertLookupAction $action)
    {
        Gate::authorize('create', Lookup::class);

        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm tra cứu mới.');
    }

    public function update(UpdateLookupRequest $request, Lookup $lookup, UpsertLookupAction $action)
    {
        Gate::authorize('manage', $lookup);

        $action->execute($request->validated(), $lookup);

        return back()->with('success', 'Đã cập nhật tra cứu.');
    }

    public function destroy(Lookup $lookup)
    {
        Gate::authorize('manage', $lookup);

        $lookup->delete();

        return back()->with('success', 'Đã xóa tra cứu.');
    }

    public function restore(Lookup $lookup)
    {
        Gate::authorize('manage', $lookup);

        $lookup->restore();

        return back()->with('success', 'Đã khôi phục tra cứu.');
    }

    public function forceDestroy(Lookup $lookup)
    {
        Gate::authorize('manage', $lookup);

        $lookup->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn tra cứu.');
    }
}
