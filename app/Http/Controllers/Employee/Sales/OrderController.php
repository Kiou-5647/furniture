<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\CancelOrderAction;
use App\Actions\Sales\CompleteOrderAction;
use App\Actions\Sales\CreateOrderAction;
use App\Actions\Sales\UpdateOrderStatusAction;
use App\Data\Sales\CreateOrderData;
use App\Data\Sales\OrderFilterData;
use App\Enums\OrderStatus;
use App\Http\Requests\Sales\CreateOrderRequest;
use App\Http\Requests\Sales\UpdateOrderStatusRequest;
use App\Http\Resources\Employee\Commerce\OrderResource;
use App\Models\Sales\Order;
use App\Services\Sales\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return Inertia::render('employee/commerce/orders/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'customerOptions' => $this->service->getCustomerOptions(),
            'orders' => Inertia::defer(fn () => OrderResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = OrderFilterData::fromRequest($request);

        return Inertia::render('employee/commerce/orders/Trash', [
            'orders' => Inertia::defer(fn () => OrderResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Order $order): Response
    {
        $order = $this->service->getById($order->id);

        return Inertia::render('employee/commerce/orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function store(CreateOrderRequest $request, CreateOrderAction $action)
    {
        $data = CreateOrderData::fromRequest($request);
        $order = $action->execute($data);

        return redirect()->route('employee.commerce.orders.show', $order)
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
}
