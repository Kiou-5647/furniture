<?php

namespace App\Http\Controllers\Employee;

use App\Actions\Setting\UpsertLookupAction;
use App\Data\LookupFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Lookup\StoreLookupRequest;
use App\Http\Requests\Employee\Lookup\UpdateLookupRequest;
use App\Models\Setting\Lookup;
use App\Services\Lookup\LookupService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LookupController extends Controller
{
    public function __construct(private LookupService $service) {}

    public function index(Request $request): Response
    {
        $filter = LookupFilterData::fromRequest($request);

        return Inertia::render('employee/lookups/Index', [
            'namespaces' => $this->service->getNamespaces(),
            'lookups' => Inertia::defer(fn () => $this->service->getByNamespace($filter)),
            'filters' => $filter,
        ]);
    }

    public function store(StoreLookupRequest $request, UpsertLookupAction $action)
    {
        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm tra cứu mới.');
    }

    public function update(UpdateLookupRequest $request, Lookup $lookup, UpsertLookupAction $action)
    {
        // The Action handles the update
        $action->execute($request->validated(), $lookup);

        return back()->with('success', 'Đã cập nhật tra cứu.');
    }

    public function destroy(Lookup $lookup)
    {
        $lookup->delete();

        return back()->with('success', 'Đã xóa tra cứu.');
    }
}
