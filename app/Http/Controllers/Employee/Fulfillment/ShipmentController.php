<?php

namespace App\Http\Controllers\Employee\Fulfillment;

use App\Actions\Fulfillment\CreateShipmentsAction;
use App\Actions\Fulfillment\DeliverShipmentAction;
use App\Actions\Fulfillment\ShipShipmentAction;
use App\Data\Fulfillment\ShipmentFilterData;
use App\Http\Resources\Employee\Fulfillment\ShipmentResource;
use App\Models\Commerce\Order;
use App\Models\Fulfillment\Shipment;
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
            'carrierOptions' => $this->service->getCarrierOptions(),
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

    public function ship(Request $request, Shipment $shipment, ShipShipmentAction $action)
    {
        $employee = $request->user()->employee;

        $request->validate([
            'carrier' => ['required', 'string', 'max:255'],
            'tracking_number' => ['required', 'string', 'max:255'],
        ]);

        $action->execute(
            $shipment,
            $request->input('carrier'),
            $request->input('tracking_number'),
            $employee
        );

        return back()->with('success', 'Đã gửi đơn vận chuyển.');
    }

    public function deliver(Shipment $shipment, Request $request, DeliverShipmentAction $action)
    {
        $employee = $request->user()->employee;

        $action->execute($shipment, $employee);

        return back()->with('success', 'Đã giao đơn vận chuyển.');
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
