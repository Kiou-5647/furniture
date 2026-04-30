<?php

namespace App\Actions\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\DB;

class ResendShipmentAction
{
    public function execute(Shipment $originalShipment, ?Employee $performedBy = null): Shipment
    {
        if ($originalShipment->status !== ShipmentStatus::Cancelled) {
            throw new \RuntimeException('Chỉ có thể gửi lại đơn đã hủy.');
        }

        if ($originalShipment->items->isEmpty()) {
            throw new \RuntimeException('Đơn vận chuyển không có sản phẩm.');
        }

        return DB::transaction(function () use ($originalShipment) {
            $newShipment = Shipment::create([
                'order_id' => $originalShipment->order_id,
                'shipment_number' => Shipment::generateShipmentNumber(),
                'origin_location_id' => $originalShipment->origin_location_id,
                'shipping_method_id' => $originalShipment->shipping_method_id,
                'status' => ShipmentStatus::Pending,
            ]);

            foreach ($originalShipment->items as $item) {
                ShipmentItem::create([
                    'shipment_id' => $newShipment->id,
                    'variant_id' => $item->variant_id,
                    'order_item_id' => $item->order_item_id,
                    'quantity_shipped' => $item->quantity_shipped,
                ]);
            }

            return $newShipment->load(['items.orderItem', 'originLocation']);
        });
    }
}
