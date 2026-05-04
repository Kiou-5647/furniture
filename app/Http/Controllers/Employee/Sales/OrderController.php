<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Fulfillment\CreateShipmentsAction;
use App\Actions\Sales\CancelOrderAction;
use App\Actions\Sales\CompleteOrderAction;
use App\Actions\Sales\CreateOrderAction;
use App\Actions\Sales\MarkOrderAsPaidAction;
use App\Actions\Sales\UpdateOrderStatusAction;
use App\Data\Sales\CreateOrderData;
use App\Data\Sales\OrderFilterData;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Exports\Sales\OrderExport;
use App\Http\Requests\Fulfillment\StoreShipmentsRequest;
use App\Http\Requests\Sales\CreateOrderRequest;
use App\Http\Requests\Sales\StockOptionsRequest;
use App\Http\Requests\Sales\UpdateOrderStatusRequest;
use App\Http\Resources\Employee\Sales\OrderResource;
use App\Models\Fulfillment\ShippingMethod;
use App\Models\Inventory\Location;
use App\Models\Sales\Order;
use App\Services\Location\StockLocatorService;
use App\Services\Sales\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class OrderController
{
    public function __construct(
        private OrderService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = OrderFilterData::fromRequest($request);
        $employeeLocationId = $request->user()->employee?->location_id;

        return Inertia::render('employee/sales/orders/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'paymentMethodOptions' => PaymentMethod::options(),
            'customerOptions' => $this->service->getCustomerOptions(),
            'sourceOptions' => [
                ['value' => 'in_store', 'label' => 'Tại quầy'],
                ['value' => 'online', 'label' => 'Trực tuyến'],
            ],
            'storeLocationOptions' => $this->service->getStoreLocationOptions(),
            'employeeLocationId' => $employeeLocationId,
            'employeeLocationName' => $employeeLocationId
                ? (Location::find($employeeLocationId)?->name)
                : null,
            'orders' => Inertia::defer(fn() => OrderResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = OrderFilterData::fromRequest($request);

        return Inertia::render('employee/sales/orders/Trash', [
            'orders' => Inertia::defer(fn() => OrderResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Order $order): Response
    {
        $order = $this->service->getById($order->id);
        $variantStockOptions = $this->service->getOrderVariantStockOptions($order);
        $order->load(['shipments.items.variant']);

        return Inertia::render('employee/sales/orders/Show', [
            'order' => new OrderResource($order),
            'variantStockOptions' => $variantStockOptions,
        ]);
    }

    public function createShipments(Order $order, CreateShipmentsAction $action)
    {
        Gate::authorize('createShipments', $order);

        try {
            $action->execute($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return response()->json([
            'order_id' => $order->id,
            'items' => $this->service->getOrderItemsForShipment($order),
        ]);
    }

    public function storeShipments(StoreShipmentsRequest $request, Order $order, CreateShipmentsAction $action)
    {
        Gate::authorize('storeShipments', $order);

        if ($order->shipments()->exists()) {
            return back()->with('error', 'Đơn hàng đã có đơn vận chuyển.');
        }

        $shipmentData = [];
        foreach ($request->validated('items') as $itemData) {
            $shipmentData[] = [
                'order_item_id' => $itemData['order_item_id'],
                'location_id' => $itemData['location_id'],
                'quantity' => $itemData['quantity'],
                'variant_id' => $itemData['variant_id']
            ];
        }

        $action->executeWithLocations($order, $shipmentData);

        return back()->with('success', 'Đã tạo đơn vận chuyển.');
    }

    public function catalog(Request $request)
    {
        $employee = $request->user()->employee;
        $employeeLocationId = $employee?->location_id;
        $shippingMethods = ShippingMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'estimated_delivery_days', 'price']);

        return response()->json([
            'customerOptions' => $this->service->getCustomerOptions(),
            'catalogItems' => $this->service->getCatalogItems($employeeLocationId),
            'bundleContents' => $this->service->getBundleContents(),
            'shippingMethods' => $shippingMethods,
            'employeeLocationId' => $employeeLocationId,
            'employeeLocationName' => $employee?->location?->name,
        ]);
    }

    public function stockOptions(StockOptionsRequest $request)
    {
        $locator = app(StockLocatorService::class);

        // Get all locations with stock for this variant
        $stockOptions = $locator->findStockForItem(
            'App\\Models\\Product\\ProductVariant',
            $request->input('variant_id')
        );

        return response()->json([
            'stock_options' => $stockOptions,
        ]);
    }

    public function store(CreateOrderRequest $request, CreateOrderAction $action)
    {
        Gate::authorize('create', Order::class);
        $employee = $request->user()->employee;

        // Merge store location into request data before creating DTO
        if (! $request->input('store_location_id') && $employee) {
            $request->merge(['store_location_id' => $employee->location_id]);
        }

        $data = CreateOrderData::fromRequest($request);
        $order = $action->execute($data);

        return redirect()->route('employee.sales.orders.show', $order)
            ->with('success', 'Đã tạo đơn hàng mới.');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $action)
    {
        Gate::authorize('updateStatus', $order);
        $employee = $request->user()->employee;
        $newStatus = OrderStatus::tryFrom($request->input('status'));

        $action->execute($order, $newStatus, $employee);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function cancel(Order $order, Request $request, CancelOrderAction $action)
    {
        Gate::authorize('cancel', $order);
        $employee = $request->user()->employee;

        $action->execute($order, $employee);

        return back()->with('success', 'Đã hủy đơn hàng.');
    }

    public function complete(Order $order, Request $request, CompleteOrderAction $action)
    {
        Gate::authorize('complete', $order);
        $employee = $request->user()->employee;

        $action->execute($order, $employee);

        return back()->with('success', 'Đã hoàn thành đơn hàng.');
    }

    public function restore(Order $order)
    {
        Gate::authorize('manage', $order);

        $order->restore();

        return back()->with('success', 'Đã khôi phục đơn hàng.');
    }

    public function destroy(Order $order)
    {
        Gate::authorize('manage', $order);

        $order->delete();

        return back()->with('success', 'Đã xóa đơn hàng.');
    }

    public function export(Request $request)
    {
        $filter = OrderFilterData::fromRequest($request);

        return Excel::download(new OrderExport($filter), 'orders_export_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function forceDestroy(Order $order)
    {
        Gate::authorize('manage', $order);

        $order->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn đơn hàng.');
    }

    public function markAsPaid(Request $request, Order $order, MarkOrderAsPaidAction $action)
    {
        Gate::authorize('markAsPaid', $order);
        try {
            $employee = $request->user()->employee;
            $action->execute($order, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận thanh toán.');
    }
}
