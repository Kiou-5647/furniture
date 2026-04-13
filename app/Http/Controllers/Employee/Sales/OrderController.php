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
use Inertia\Inertia;
use Inertia\Response;

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
            'orders' => Inertia::defer(fn () => OrderResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = OrderFilterData::fromRequest($request);

        return Inertia::render('employee/sales/orders/Trash', [
            'orders' => Inertia::defer(fn () => OrderResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Order $order): Response
    {
        $order = $this->service->getById($order->id);

        // Get stock options for each unique variant in the shipments
        $variantIds = collect();
        foreach ($order->shipments as $shipment) {
            foreach ($shipment->items as $item) {
                if ($item->orderItem && str_contains($item->orderItem->purchasable_type, 'ProductVariant')) {
                    $variantIds->push($item->orderItem->purchasable_id);
                }
            }
        }

        $variantStockOptions = [];
        foreach ($variantIds->unique() as $variantId) {
            $variantStockOptions[$variantId] = $this->service->getVariantStockOptions($variantId);
        }

        return Inertia::render('employee/sales/orders/Show', [
            'order' => new OrderResource($order),
            'variantStockOptions' => $variantStockOptions,
        ]);
    }

    public function createShipments(Order $order)
    {
        // Only allow if no shipments exist yet and order is not completed/cancelled
        if ($order->shipments()->exists()) {
            return back()->with('error', 'Đơn hàng đã có đơn vận chuyển.');
        }

        if (in_array($order->status->value, ['completed', 'cancelled'], true)) {
            return back()->with('error', 'Không thể tạo đơn vận chuyển cho đơn hàng này.');
        }

        // Prepaid orders must be paid first
        if ($order->isPrepaid() && ! $order->paid_at) {
            return back()->with('error', 'Đơn hàng chưa được thanh toán.');
        }

        $locator = app(StockLocatorService::class);
        $itemsWithStock = [];

        foreach ($order->items()->with('purchasable')->get() as $item) {
            if (str_contains($item->purchasable_type, 'ProductVariant')) {
                $stockOptions = $locator->findStockForItem(
                    $item->purchasable_type,
                    $item->purchasable_id
                );

                $itemsWithStock[] = [
                    'id' => $item->id,
                    'purchasable_name' => $item->purchasable?->name ?? 'Sản phẩm',
                    'quantity' => $item->quantity,
                    'stock_options' => $stockOptions->toArray(),
                ];
            }
        }

        return response()->json([
            'order_id' => $order->id,
            'items' => $itemsWithStock,
        ]);
    }

    public function storeShipments(StoreShipmentsRequest $request, Order $order)
    {
        if ($order->shipments()->exists()) {
            return back()->with('error', 'Đơn hàng đã có đơn vận chuyển.');
        }

        $shipmentData = [];
        foreach ($request->validated('items') as $itemData) {
            $shipmentData[] = [
                'order_item_id' => $itemData['order_item_id'],
                'location_id' => $itemData['location_id'],
                'quantity' => $itemData['quantity'],
            ];
        }

        $createAction = app(CreateShipmentsAction::class);
        $createAction->executeWithLocations($order, $shipmentData);

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
        $employee = $request->user()->employee;
        $newStatus = OrderStatus::tryFrom($request->input('status'));

        $action->execute($order, $newStatus, $employee);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function cancel(Order $order, Request $request, CancelOrderAction $action)
    {
        $employee = $request->user()->employee;

        $action->execute($order, $employee);

        return back()->with('success', 'Đã hủy đơn hàng.');
    }

    public function complete(Order $order, Request $request, CompleteOrderAction $action)
    {
        $employee = $request->user()->employee;

        $action->execute($order, $employee);

        return back()->with('success', 'Đã hoàn thành đơn hàng.');
    }

    public function restore(Order $order)
    {
        if (! Auth::user()->can('orders.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $order->restore();

        return back()->with('success', 'Đã khôi phục đơn hàng.');
    }

    public function destroy(Order $order)
    {
        if (! Auth::user()->can('orders.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $order->delete();

        return back()->with('success', 'Đã xóa đơn hàng.');
    }

    public function forceDestroy(Order $order)
    {
        if (! Auth::user()->can('orders.manage')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $order->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn đơn hàng.');
    }

    public function markAsPaid(Request $request, Order $order, MarkOrderAsPaidAction $action)
    {
        try {
            $employee = $request->user()->employee;
            $action->execute($order, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận thanh toán.');
    }
}
