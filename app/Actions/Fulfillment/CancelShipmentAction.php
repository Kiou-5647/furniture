<?php

namespace App\Actions\Fulfillment;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\DB;

class CancelShipmentAction
{
    public function execute(Shipment $shipment, ?Employee $performedBy = null): Shipment
    {
        if (! $shipment->canBeCancelled()) {
            throw new \RuntimeException('Đơn vận chuyển không thể hủy.');
        }

        DB::transaction(function () use ($shipment) {
            $shipment->update([
                'status' => ShipmentStatus::Cancelled,
            ]);

            // If all shipments are cancelled, revert order to Processing
            $this->checkOrderRevert($shipment);
        });

        return $shipment->refresh();
    }

    protected function checkOrderRevert(Shipment $shipment): void
    {
        $order = $shipment->order;

        $allCancelled = $order->shipments()
            ->where('status', '!=', ShipmentStatus::Cancelled)
            ->exists() === false;

        if ($allCancelled) {
            $order->update(['status' => OrderStatus::Processing]);
        }
    }
}
