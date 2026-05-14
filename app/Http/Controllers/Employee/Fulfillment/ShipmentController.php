<?php

namespace App\Http\Controllers\Employee\Fulfillment;

use App\Actions\Fulfillment\CancelShipmentAction;
use App\Actions\Fulfillment\CreateShipmentsAction;
use App\Actions\Fulfillment\DeliverShipmentAction;
use App\Actions\Fulfillment\ResendShipmentAction;
use App\Actions\Fulfillment\ReturnShipmentItemAction;
use App\Actions\Fulfillment\ShipShipmentAction;
use App\Data\Fulfillment\ShipmentFilterData;
use App\Enums\ShipmentStatus;
use App\Http\Requests\Fulfillment\StoreShipmentsRequest;
use App\Http\Resources\Employee\Fulfillment\ShipmentResource;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Sales\Order;
use App\Services\Fulfillment\ShipmentService;
use App\Services\Sales\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ShipmentController
{
    public function __construct(
        private ShipmentService $shipmentService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        if (! Gate::allows('viewAny', Shipment::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập danh sách đơn vận chuyển!');
        }

        $filter = ShipmentFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipments/Index', [
            'statusOptions' => ShipmentStatus::options(),
            'shipments' => Inertia::defer(fn () => ShipmentResource::collection(
                $this->shipmentService->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Request $request, Shipment $shipment)
    {
        if (! Gate::allows('view', $shipment)) {
            return back()->with('error', 'Bạn không có quyền xem chi tiết đơn vận chuyển này!');
        }

        $shipment = $this->shipmentService->getById($shipment->id, $request->user());

        return Inertia::render('employee/fulfillment/shipments/Show', [
            'shipment' => new ShipmentResource($shipment),
        ]);
    }

    public function createShipments(Order $order, CreateShipmentsAction $action)
    {
        if (! Gate::allows('createShipments', $order)) {
            return back()->with('error', 'Bạn không có quyền tạo vận chuyển cho đơn hàng này!');
        }

        try {
            $action->execute($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return response()->json([
            'order_id' => $order->id,
            'items' => $this->orderService->getOrderItemsForShipment($order),
        ]);
    }

    public function storeShipments(StoreShipmentsRequest $request, Order $order, CreateShipmentsAction $action)
    {
        if (! Gate::allows('storeShipments', $order)) {
            return back()->with('error', 'Bạn không có quyền lưu vận chuyển cho đơn hàng này!');
        }

        if ($order->shipments()->exists()) {
            return back()->with('error', 'Đơn hàng đã có đơn vận chuyển.');
        }

        $shipmentData = [];
        foreach ($request->validated('items') as $itemData) {
            $shipmentData[] = [
                'order_item_id' => $itemData['order_item_id'],
                'location_id' => $itemData['location_id'],
                'quantity' => $itemData['quantity'],
                'variant_id' => $itemData['variant_id'],
            ];
        }

        try {
            $action->executeWithLocations($order, $shipmentData);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã tạo đơn vận chuyển.');
    }

    public function ship(Shipment $shipment, ShipShipmentAction $action)
    {
        if (! Gate::allows('ship', $shipment)) {
            return back()->with('error', 'Bạn không có quyền gửi đơn vận chuyển này!');
        }

        $employee = request()->user()->employee;

        try {
            $action->execute($shipment, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã gửi đơn vận chuyển.');
    }

    public function deliver(Shipment $shipment, Request $request, DeliverShipmentAction $action)
    {
        if (! Gate::allows('deliver', $shipment)) {
            return back()->with('error', 'Bạn không có quyền giao đơn vận chuyển này!');
        }

        $employee = $request->user()->employee;

        try {
            $action->execute($shipment, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã giao đơn vận chuyển.');
    }

    public function cancel(Shipment $shipment, CancelShipmentAction $action)
    {
        if (! Gate::allows('cancel', $shipment)) {
            return back()->with('error', 'Bạn không có quyền hủy đơn vận chuyển này!');
        }

        $employee = request()->user()->employee;

        try {
            $action->execute($shipment, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã hủy đơn vận chuyển.');
    }

    public function resend(Shipment $shipment, ResendShipmentAction $action)
    {
        if (! Gate::allows('resend', $shipment)) {
            return back()->with('error', 'Bạn không có quyền gửi lại đơn vận chuyển này!');
        }

        $employee = request()->user()->employee;

        try {
            $newShipment = $action->execute($shipment, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('employee.fulfillment.shipments.show', $newShipment)
            ->with('success', 'Đã tạo đơn vận chuyển mới.');
    }

    public function returnItem(Shipment $shipment, ShipmentItem $shipmentItem, ReturnShipmentItemAction $action)
    {
        if (! Gate::allows('returnItem', $shipment)) {
            return back()->with('error', 'Bạn không có quyền đánh dấu trả hàng cho đơn này!');
        }

        if ($shipmentItem->shipment_id !== $shipment->id) {
            return back()->with('error', 'Sản phẩm không thuộc đơn vận chuyển này.');
        }

        $employee = request()->user()->employee;
        $reason = request()->input('reason', '');

        try {
            $action->execute($shipmentItem, $reason, $employee);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã đánh dấu trả hàng.');
    }

    public function destroy(Shipment $shipment)
    {
        if (! Gate::allows('delete', $shipment)) {
            return back()->with('error', 'Không đủ quyền hạn để xóa đơn vận chuyển này!');
        }

        $shipment->delete();

        return back()->with('success', 'Đã xóa đơn vận chuyển.');
    }
}
