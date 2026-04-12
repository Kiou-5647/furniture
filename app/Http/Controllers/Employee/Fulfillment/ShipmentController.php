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
use App\Http\Resources\Employee\Fulfillment\ShipmentResource;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Sales\Order;
use App\Services\Fulfillment\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController
{
    public function __construct(
        private ShipmentService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = ShipmentFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipments/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'shipments' => Inertia::defer(fn () => ShipmentResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = ShipmentFilterData::fromRequest($request);

        return Inertia::render('employee/fulfillment/shipments/Trash', [
            'shipments' => Inertia::defer(fn () => ShipmentResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Shipment $shipment): Response
    {
        $shipment = $this->service->getById($shipment->id);

        return Inertia::render('employee/fulfillment/shipments/Show', [
            'shipment' => new ShipmentResource($shipment),
        ]);
    }

    public function createShipments(Request $request, CreateShipmentsAction $action)
    {
        $request->validate(['order_id' => ['required', 'uuid', 'exists:orders,id']]);

        $order = Order::with('items.purchasable')->findOrFail($request->input('order_id'));
        $action->execute($order);

        return back()->with('success', 'Đã điều phối vận chuyển.');
    }

    public function ship(Shipment $shipment, ShipShipmentAction $action)
    {
        $employee = request()->user()->employee;

        $action->execute($shipment, $employee);

        return back()->with('success', 'Đã gửi đơn vận chuyển.');
    }

    public function deliver(Shipment $shipment, Request $request, DeliverShipmentAction $action)
    {
        $employee = $request->user()->employee;

        $action->execute($shipment, $employee);

        return back()->with('success', 'Đã giao đơn vận chuyển.');
    }

    public function cancel(Shipment $shipment, CancelShipmentAction $action)
    {
        $employee = request()->user()->employee;

        $action->execute($shipment, $employee);

        return back()->with('success', 'Đã hủy đơn vận chuyển.');
    }

    public function resend(Shipment $shipment, ResendShipmentAction $action)
    {
        $employee = request()->user()->employee;

        $newShipment = $action->execute($shipment, $employee);

        return redirect()->route('employee.fulfillment.shipments.show', $newShipment)
            ->with('success', 'Đã tạo đơn vận chuyển mới.');
    }

    public function returnItem(Shipment $shipment, ShipmentItem $shipmentItem, ReturnShipmentItemAction $action)
    {
        if ($shipmentItem->shipment_id !== $shipment->id) {
            return back()->with('error', 'Sản phẩm không thuộc đơn vận chuyển này.');
        }

        $employee = request()->user()->employee;
        $reason = request()->input('reason', '');

        $action->execute($shipmentItem, $reason, $employee);

        return back()->with('success', 'Đã đánh dấu trả hàng.');
    }

    public function updateItemLocation(Shipment $shipment, ShipmentItem $shipmentItem)
    {
        if ($shipmentItem->shipment_id !== $shipment->id) {
            return back()->with('error', 'Sản phẩm không thuộc đơn vận chuyển này.');
        }

        if ($shipment->status !== ShipmentStatus::Pending) {
            return back()->with('error', 'Chỉ có thể đổi kho khi đơn chưa xuất kho.');
        }

        $request = request()->validate([
            'source_location_id' => ['required', 'uuid', 'exists:locations,id'],
        ]);

        $newLocationId = $request['source_location_id'];
        $order = $shipment->order;

        // Update the item's source location
        $shipmentItem->update([
            'source_location_id' => $newLocationId,
        ]);

        // Check if there's already a shipment for this order with the target location
        $targetShipment = $order->shipments()
            ->where('origin_location_id', $newLocationId)
            ->where('id', '!=', $shipment->id)
            ->first();

        if ($targetShipment) {
            // Move item to existing shipment with matching location
            $shipmentItem->update([
                'shipment_id' => $targetShipment->id,
            ]);
        } elseif ($shipment->items()->count() === 1) {
            // Only one item in this shipment — just update the shipment's origin
            $shipment->update([
                'origin_location_id' => $newLocationId,
            ]);
        } else {
            // Multiple items, no matching shipment — create a new shipment
            $newShipment = Shipment::create([
                'order_id' => $order->id,
                'shipment_number' => Shipment::generateShipmentNumber(),
                'origin_location_id' => $newLocationId,
                'shipping_method_id' => $order->shipping_method_id,
                'status' => ShipmentStatus::Pending,
            ]);

            $shipmentItem->update([
                'shipment_id' => $newShipment->id,
            ]);
        }

        // Clean up empty shipments
        $shipment->load('items');
        if ($shipment->items->isEmpty()) {
            $shipment->delete();
        }

        return back()->with('success', 'Đã cập nhật nguồn kho.');
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
