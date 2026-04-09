<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Actions\Inventory\CancelStockTransferAction;
use App\Actions\Inventory\CreateStockTransferAction;
use App\Actions\Inventory\ReceiveStockTransferAction;
use App\Actions\Inventory\ShipStockTransferAction;
use App\Data\Inventory\StockTransferFilterData;
use App\Http\Requests\Inventory\ReceiveStockTransferRequest;
use App\Http\Requests\Inventory\StoreStockTransferRequest;
use App\Http\Resources\Employee\Inventory\StockTransferResource;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use App\Services\Inventory\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockTransferController
{
    public function __construct(
        private StockTransferService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = StockTransferFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/transfers/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'locationOptions' => $this->service->getLocationOptions(),
            'transfers' => Inertia::defer(fn () => StockTransferResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('employee/inventory/transfers/Create', [
            'locationOptions' => $this->service->getLocationOptions(),
        ]);
    }

    public function store(StoreStockTransferRequest $request, CreateStockTransferAction $action)
    {
        $validated = $request->validated();

        $fromLocation = Location::findOrFail($validated['from_location_id']);
        $toLocation = Location::findOrFail($validated['to_location_id']);

        $employee = $request->user()->employee;

        $action->handle(
            fromLocation: $fromLocation,
            toLocation: $toLocation,
            items: $validated['items'],
            requestedBy: $employee,
            notes: $validated['notes'] ?? null,
        );

        return redirect()->route('employee.inventory.transfers.index')
            ->with('success', 'Đã tạo phiếu chuyển kho.');
    }

    public function show(StockTransfer $transfer): Response
    {
        $transfer = $this->service->getById($transfer->id);

        return Inertia::render('employee/inventory/transfers/Show', [
            'transfer' => (new StockTransferResource($transfer))->resolve(),
        ]);
    }

    public function ship(StockTransfer $transfer, Request $request, ShipStockTransferAction $action)
    {
        $employee = $request->user()->employee;

        $action->handle(
            transfer: $transfer,
            performedBy: $employee,
        );

        return back()->with('success', 'Đã xuất kho phiếu chuyển kho.');
    }

    public function receive(ReceiveStockTransferRequest $request, StockTransfer $transfer, ReceiveStockTransferAction $action)
    {
        $validated = $request->validated();
        $employee = $request->user()->employee;

        $action->handle(
            transfer: $transfer,
            receivedQuantities: $validated['items'],
            receivedBy: $employee,
        );

        return back()->with('success', 'Đã nhận hàng chuyển kho.');
    }

    public function cancel(StockTransfer $transfer, Request $request, CancelStockTransferAction $action)
    {
        $employee = $request->user()->employee;

        $action->handle(
            transfer: $transfer,
            performedBy: $employee,
        );

        return back()->with('success', 'Đã hủy phiếu chuyển kho.');
    }

    public function variants(string $locationId): JsonResponse
    {
        $variants = Inventory::query()
            ->where('location_id', $locationId)
            ->where('quantity', '>', 0)
            ->with('variant:id,sku,name,product_id,option_values,price', 'variant.product:id,name')
            ->get()
            ->map(fn (Inventory $inventory) => [
                'id' => $inventory->variant->id,
                'sku' => $inventory->variant->sku,
                'name' => $inventory->variant->name,
                'product_name' => $inventory->variant->product?->name,
                'available_quantity' => $inventory->quantity,
                'option_values' => $inventory->variant->option_values,
                'price' => $inventory->variant->price,
                'image_url' => $inventory->variant->getFirstMediaUrl('primary_image', 'thumb'),
                'full_image_url' => $inventory->variant->getFirstMediaUrl('primary_image'),
            ]);

        return response()->json($variants);
    }

    public function locations(string $variantId): JsonResponse
    {
        $locations = Inventory::query()
            ->where('variant_id', $variantId)
            ->where('quantity', '>', 0)
            ->with('location:id,code,name,type')
            ->get()
            ->map(fn (Inventory $inventory) => [
                'id' => $inventory->location->id,
                'code' => $inventory->location->code,
                'name' => $inventory->location->name,
                'type' => $inventory->location->type->value,
                'available_quantity' => $inventory->quantity,
            ]);

        return response()->json($locations);
    }
}
