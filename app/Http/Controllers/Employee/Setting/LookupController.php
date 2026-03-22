<?php

namespace App\Http\Controllers\Employee\Setting;

use App\Actions\Setting\UpsertLookupAction;
use App\Data\Setting\LookupFilterData;
use App\Enums\LookupType;
use App\Http\Requests\Setting\Lookup\StoreLookupRequest;
use App\Http\Requests\Setting\Lookup\UpdateLookupRequest;
use App\Models\Setting\Lookup;
use App\Services\Setting\LookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LookupController
{
    public function __construct(private LookupService $service) {}

    public function index(Request $request, ?string $namespace = LookupType::Colors->value): Response
    {
        $filter = LookupFilterData::fromRequest($request, $namespace);

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
        if (! Auth::user()->can('lookups.manage')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa tra cứu!');
        }

        $lookup->delete();

        return back()->with('success', 'Đã xóa tra cứu.');
    }

    public function forceDestroy(Lookup $lookup)
    {
        if (! Auth::user()->can('lookups.manage')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa tra cứu!');
        }

        $lookup->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn tra cứu.');
    }
}
