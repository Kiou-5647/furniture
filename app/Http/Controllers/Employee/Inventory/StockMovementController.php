<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Data\Inventory\StockMovementFilterData;
use App\Http\Resources\Employee\Inventory\StockMovementResource;
use App\Services\Inventory\StockMovementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController
{
    public function __construct(
        private StockMovementService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = StockMovementFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/movements/Index', [
            'typeOptions' => $this->service->getTypeOptions(),
            'locationOptions' => $this->service->getLocationOptions(),
            'variantOptions' => Inertia::defer(fn () => $this->service->getVariantOptions()),
            'movements' => Inertia::defer(fn () => StockMovementResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }
}
