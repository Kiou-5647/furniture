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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StockTransferController
{
    public function __construct(
        private StockTransferService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', StockTransfer::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập danh sách chuyển kho.');
        }

        $filter = StockTransferFilterData::fromRequest($request);

        return Inertia::render('employee/inventory/transfers/Index', [
            'canCreate' => Gate::allows('create', StockTransfer::class),
            'statusOptions' => $this->service->getStatusOptions(),
            'locationOptions' => $this->service->getLocationOptions(),
            'transfers' => Inertia::defer(fn() => StockTransferResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function create()
    {
        if (!Gate::allows('create', StockTransfer::class)) {
            return back()->with('error', 'Bạn không có quyền tạo phiếu chuyển kho.');
        }

        return Inertia::render('employee/inventory/transfers/Create', [
            'locationOptions' => $this->service->getLocationOptionsForFromLocation(Auth::user()),
            'toLocationOptions' => $this->service->getLocationOptions(),
        ]);
    }

    public function store(StoreStockTransferRequest $request, CreateStockTransferAction $action)
    {
        if (!Gate::allows('create', StockTransfer::class)) {
            return back()->with('error', 'Bạn không có quyền tạo phiếu chuyển kho.');
        }

        $validated = $request->validated();

        $fromLocation = Location::findOrFail($validated['from_location_id']);
        $toLocation = Location::findOrFail($validated['to_location_id']);

        $employee = $request->user()->employee;

        try {
            $action->handle(
                fromLocation: $fromLocation,
                toLocation: $toLocation,
                items: $validated['items'],
                requestedBy: $employee,
                notes: $validated['notes'] ?? null,
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('employee.inventory.transfers.index');
    }

    public function show(StockTransfer $transfer)
    {
        if (!Gate::allows('view', $transfer)) {
            return back()->with('error', 'Bạn không có quyền xem chi tiết phiếu chuyển kho này.');
        }

        $transfer = $this->service->getById($transfer->id);

        return Inertia::render('employee/inventory/transfers/Show', [
            'transfer' => (new StockTransferResource($transfer))->resolve(),
        ]);
    }

    public function ship(StockTransfer $transfer, Request $request, ShipStockTransferAction $action)
    {
        if (!Gate::allows('ship', $transfer)) {
            return back()->with('error', 'Bạn không có quyền xuất kho cho phiếu chuyển này.');
        }

        $employee = $request->user()->employee;

        try {
            $action->handle(
                transfer: $transfer,
                performedBy: $employee,
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xuất kho phiếu chuyển kho.');
    }

    public function receive(ReceiveStockTransferRequest $request, StockTransfer $transfer, ReceiveStockTransferAction $action)
    {
        if (!Gate::allows('receive', $transfer)) {
            return back()->with('error', 'Bạn không có quyền nhận hàng cho phiếu chuyển này.');
        }

        $validated = $request->validated();
        $employee = $request->user()->employee;

        try {
            $action->handle(
                transfer: $transfer,
                receivedQuantities: $validated['items'],
                receivedBy: $employee,
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã nhận hàng chuyển kho.');
    }

    public function cancel(StockTransfer $transfer, Request $request, CancelStockTransferAction $action)
    {
        if (!Gate::allows('cancel', $transfer)) {
            return back()->with('error', 'Bạn không có quyền hủy phiếu chuyển kho này.');
        }

        $employee = $request->user()->employee;

        try {
            $action->handle(
                transfer: $transfer,
                performedBy: $employee,
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã hủy phiếu chuyển kho.');
    }

    public function destroy(StockTransfer $transfer)
    {
        if (!Gate::allows('delete', $transfer)) {
            return back()->with('error', 'Bạn không có quyền xóa phiếu chuyển kho này.');
        }

        $transfer->delete();

        return back()->with('success', 'Đã xóa phiếu chuyển kho.');
    }

    public function variants(string $locationId): JsonResponse
    {
        $variants = Inventory::query()
            ->where('location_id', $locationId)
            ->where('quantity', '>', 0)
            ->with('variant:id,sku,name,product_id,option_values,price', 'variant.product:id,name')
            ->get()
            ->map(fn(Inventory $inventory) => [
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
            ->map(fn(Inventory $inventory) => [
                'id' => $inventory->location->id,
                'code' => $inventory->location->code,
                'name' => $inventory->location->name,
                'type' => $inventory->location->type->value,
                'available_quantity' => $inventory->quantity,
            ]);

        return response()->json($locations);
    }
}
