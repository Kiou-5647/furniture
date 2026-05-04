<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\DB;

class DeliverShipmentAction
{
    public function execute(Shipment $shipment, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeDelivered()) {
            throw new \RuntimeException('Đơn vận chuyển không thể giao.');
        }

        DB::transaction(function () use ($shipment, $performedBy) {
            // Mark only non-cancelled items as delivered
            $items = $shipment->items()
                ->whereNotIn('status', [ShipmentStatus::Cancelled, ShipmentStatus::Returned])
                ->get();

            foreach ($items as $item) {
                $item->update(['status' => ShipmentStatus::Delivered]);
            }

            $shipment->update([
                'status' => ShipmentStatus::Delivered,
                'handled_by' => $performedBy?->id ?? $shipment->handled_by,
            ]);
        });

        return $shipment->refresh();
    }
}
