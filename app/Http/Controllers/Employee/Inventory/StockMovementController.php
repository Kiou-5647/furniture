<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Data\Inventory\StockMovementFilterData;
use App\Http\Resources\Employee\Inventory\StockMovementResource;
use App\Services\Inventory\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController
{
    public function __construct(
        private StockMovementService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', \App\Models\Inventory\StockMovement::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập lịch sử tồn kho.');
        }

        $filter = StockMovementFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/movements/Index', [
            'typeOptions' => $this->service->getTypeOptions(),
            'locationOptions' => $this->service->getLocationOptions(),
            'variantOptions' => Inertia::defer(fn() => $this->service->getVariantOptions()),
            'movements' => Inertia::defer(fn() => StockMovementResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }
}
