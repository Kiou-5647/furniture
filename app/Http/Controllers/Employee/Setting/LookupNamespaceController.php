<?php

namespace App\Http\Controllers\Employee\Setting;

use App\Http\Requests\Setting\LookupNamespace\StoreLookupNamespaceRequest;
use App\Http\Requests\Setting\LookupNamespace\UpdateLookupNamespaceRequest;
use App\Http\Resources\Setting\LookupNamespace\EmployeeLookupNamespaceResource;
use App\Models\Setting\LookupNamespace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LookupNamespaceController
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->cookie('per_page', 15);

        $query = LookupNamespace::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('for_variants')) {
            $query->where('for_variants', $request->boolean('for_variants'));
        }

        $orderBy = $request->input('order_by', 'is_system');
        $orderDirection = $request->input('order_direction', 'desc');

        if ($orderBy === 'is_system') {
            $query->orderBy('is_system', 'desc')
                ->orderBy('created_at', 'asc');
        } else {
            $query->orderBy($orderBy, $orderDirection);
        }

        return Inertia::render('employee/settings/lookup-namespaces/Index', [
            'namespaces' => Inertia::defer(fn () => EmployeeLookupNamespaceResource::collection(
                $query->paginate($perPage)
            )),
            'filters' => [
                'search' => $request->input('search'),
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
                'for_variants' => $request->has('for_variants') ? $request->boolean('for_variants') : null,
                'order_by' => $request->input('order_by'),
                'order_direction' => $request->input('order_direction'),
            ],
        ]);
    }

    public function store(StoreLookupNamespaceRequest $request)
    {
        LookupNamespace::create($request->validated());

        return back()->with('success', 'Đã thêm danh mục tra cứu mới.');
    }

    public function update(UpdateLookupNamespaceRequest $request, LookupNamespace $lookupNamespace)
    {
        $lookupNamespace->update($request->validated());

        return back()->with('success', 'Đã cập nhật danh mục tra cứu.');
    }

    public function destroy(LookupNamespace $lookupNamespace)
    {
        if (! Auth::user()->can('lookups.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        if ($lookupNamespace->is_system) {
            return back()->with('error', 'Không thể xóa danh mục hệ thống!');
        }

        $lookupNamespace->delete();

        return back()->with('success', 'Đã xóa danh mục tra cứu.');
    }
}
