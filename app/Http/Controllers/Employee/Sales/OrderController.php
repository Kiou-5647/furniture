<?php

namespace App\Http\Controllers\Employee\Sales;

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
use Maatwebsite\Excel\Facades\Excel;

class OrderController
{
    public function __construct(
        private OrderService $service,
    ) {}

    public function index(Request $request)
    {
        if (! Gate::allows('viewAny', Order::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập danh sách đơn hàng!');
        }

        $filter = OrderFilterData::fromRequest($request);
        $user = $request->user();
        $employeeLocationId = $user->employee?->store_location_id;

        return Inertia::render('employee/sales/orders/Index', [
            'orders' => Inertia::defer(fn() => OrderResource::collection(
                $this->service->getFiltered($filter, $user)
            )),
            'statusOptions' => OrderStatus::options(),
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
            'filters' => $filter,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        if (! Gate::allows('view', $order)) {
            return back()->with('error', 'Bạn không có quyền xem chi tiết đơn hàng này!');
        }

        $order = $this->service->getById($order->id, $request->user());
        $variantStockOptions = $this->service->getOrderVariantStockOptions($order);
        $order->load(['shipments.items.variant', 'shipments.originLocation']);

        return Inertia::render('employee/sales/orders/Show', [
            'order' => new OrderResource($order),
            'variantStockOptions' => $variantStockOptions,
        ]);
    }

    public function store(CreateOrderRequest $request, CreateOrderAction $action)
    {
        if (! Gate::allows('create', Order::class)) {
            return back()->with('error', 'Bạn không có quyền tạo đơn hàng!');
        }

        $employee = $request->user()->employee;

        if (! $request->input('store_location_id') && $employee) {
            $request->merge(['store_location_id' => $employee->store_location_id]);
        }

        $data = CreateOrderData::fromRequest($request);

        try {
            $order = $action->execute($data, $employee->id);

            return redirect()->route('employee.sales.orders.show', $order)
                ->with('success', 'Đã tạo đơn hàng mới.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $action)
    {
        if (! Gate::allows('updateStatus', $order)) {
            return back()->with('error', 'Bạn không có quyền cập nhật trạng thái đơn hàng này!');
        }

        $employee = $request->user()->employee;
        $newStatus = OrderStatus::tryFrom($request->input('status'));

        try {
            $action->execute($order, $newStatus, $employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function cancel(Order $order, Request $request, CancelOrderAction $action)
    {
        if (! Gate::allows('cancel', $order)) {
            return back()->with('error', 'Bạn không có quyền hủy đơn hàng này!');
        }

        $employee = $request->user()->employee;

        try {
            $action->execute($order, $employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã hủy đơn hàng.');
    }

    public function complete(Order $order, Request $request, CompleteOrderAction $action)
    {
        if (! Gate::allows('complete', $order)) {
            return back()->with('error', 'Bạn không có quyền hoàn thành đơn hàng này!');
        }

        $employee = $request->user()->employee;

        try {
            $action->execute($order, $employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã hoàn thành đơn hàng.');
    }

    public function destroy(Order $order)
    {
        if (! Gate::allows('delete', $order)) {
            return back()->with('error', 'Bạn không có quyền xóa đơn hàng này!');
        }

        $order->delete();

        return back()->with('success', 'Đã xóa đơn hàng.');
    }

    public function export(Request $request)
    {
        $filter = OrderFilterData::fromRequest($request);

        return Excel::download(new OrderExport($filter), 'orders_export_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function markAsPaid(Request $request, Order $order, MarkOrderAsPaidAction $action)
    {
        if (! Gate::allows('markAsPaid', $order)) {
            return back()->with('error', 'Bạn không có quyền xác nhận thanh toán cho đơn hàng này!');
        }

        try {
            $employee = $request->user()->employee;
            $action->execute($order, $employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận thanh toán.');
    }

    public function catalog(Request $request)
    {
        $employee = $request->user()->employee;
        $employeeLocationId = $employee?->store_location_id;
        $shippingMethods = ShippingMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'estimated_delivery_days', 'price']);

        return response()->json([
            'customerOptions' => $this->service->getCustomerOptions(),
            'catalogItems' => $this->service->getCatalogItems($employeeLocationId),
            'bundleContents' => $this->service->getBundleContents(),
            'shippingMethods' => $shippingMethods,
            'employeeLocationId' => $employeeLocationId,
            'employeeLocationName' => $employee?->storeLocation?->name,
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
}
