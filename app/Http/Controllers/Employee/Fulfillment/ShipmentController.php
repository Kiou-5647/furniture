<?php

namespace App\Http\Controllers\Employee\Fulfillment;

use App\Actions\Fulfillment\CancelShipmentAction;
use App\Actions\Fulfillment\CreateShipmentsAction;
use App\Actions\Fulfillment\DeliverShipmentAction;
use App\Actions\Fulfillment\ResendShipmentAction;
use App\Actions\Fulfillment\ReturnShipmentItemAction;
use App\Actions\Fulfillment\ShipShipmentAction;
use App\Data\Fulfillment\ShipmentFilterData;
use App\Http\Resources\Employee\Fulfillment\ShipmentResource;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Sales\Order;
use App\Services\Fulfillment\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController
{
    public function __construct(
        private ShipmentService $shipmentService,
    ) {}

    public function index(Request $request): Response
    {
        $filter = ShipmentFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipments/Index', [
            'statusOptions' => $this->shipmentService->getStatusOptions(),
            'shipments' => Inertia::defer(fn() => ShipmentResource::collection(
                $this->shipmentService->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = ShipmentFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipments/Trash', [
            'shipments' => Inertia::defer(fn() => ShipmentResource::collection(
                $this->shipmentService->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Shipment $shipment): Response
    {
        $shipment = $this->shipmentService->getById($shipment->id);

        return Inertia::render('employee/fulfillment/shipments/Show', [
            'shipment' => new ShipmentResource($shipment),
        ]);
    }

    public function createShipments(Request $request, CreateShipmentsAction $action)
    {
        $request->validate(['order_id' => ['required', 'uuid', 'exists:orders,id']]);

        $order = Order::with('items.purchasable')->findOrFail($request->input('order_id'));

        try {
            $action->execute($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã điều phối vận chuyển.');
    }

    public function ship(Shipment $shipment, ShipShipmentAction $action)
    {
        Gate::authorize('ship', $shipment);
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
        Gate::authorize('deliver', $shipment);
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
        Gate::authorize('cancel', $shipment);
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
        Gate::authorize('resend', $shipment);
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
        Gate::authorize('returnItem', $shipment);

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
        if (! Auth::user()->can('shipments.delete')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $shipment->delete();

        return back()->with('success', 'Đã xóa đơn vận chuyển.');
    }

    public function restore(Shipment $shipment)
    {
        if (! Auth::user()->can('shipments.restore')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $shipment->restore();

        return back()->with('success', 'Đã khôi phục đơn vận chuyển.');
    }

    public function forceDestroy(Shipment $shipment)
    {
        if (! Auth::user()->can('shipments.force_delete')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $shipment->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn đơn vận chuyển.');
    }
}
