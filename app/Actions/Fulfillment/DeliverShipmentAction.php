<?php

namespace App\Actions\Fulfillment;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;
use Illuminate\Support\Facades\DB;

class DeliverShipmentAction
{
    public function execute(Shipment $shipment, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeDelivered()) {
            throw new \RuntimeException('Đơn vận chuyển không thể giao.');
        }

        DB::transaction(function () use ($shipment, $performedBy) {
            $shipment->update([
                'status' => ShipmentStatus::Delivered,
                'handled_by' => $performedBy?->user_id ?? $shipment->handled_by,
            ]);

            // Check if all shipments for the order are delivered → complete order
            $this->checkOrderCompletion($shipment);
        });

        return $shipment->refresh();
    }

    protected function checkOrderCompletion(Shipment $shipment): void
    {
        $order = $shipment->order;

        $allDelivered = $order->shipments()
            ->where('status', '!=', ShipmentStatus::Delivered)
            ->exists() === false;

        if ($allDelivered && $order->canBeCompleted()) {
            $order->update(['status' => OrderStatus::Completed]);
        }
    }
}
