<?php

namespace App\Actions\Fulfillment;

use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Sales\Order;
use App\Services\Fulfillment\FulfillmentRouterService;
use Illuminate\Support\Facades\DB;

class CreateShipmentsAction
{
    public function __construct(
        private FulfillmentRouterService $router,
    ) {}

    public function execute(Order $order): void
    {
        if ($order->shipments()->exists()) {
            throw new \RuntimeException('Đơn hàng đã được điều phối vận chuyển.');
        }

        if (! $order->shipping_method_id) {
            throw new \RuntimeException('Đơn hàng không cần vận chuyển.');
        }

        // COD orders can ship without payment (collect on delivery)
        // Prepaid orders must be paid first
        $paymentMethod = is_string($order->payment_method)
            ? PaymentMethod::tryFrom($order->payment_method)
            : $order->payment_method;

        if ($paymentMethod && $paymentMethod->isPrepaid() && ! $order->paid_at) {
            throw new \RuntimeException('Đơn hàng chưa được thanh toán, chưa tạo vận chuyển.');
        }

        $this->router->routeOrder($order);
    }

    /**
     * Create shipments with explicit location assignments from employee.
     *
     * @param  array  $shipmentData  [{order_item_id, location_id, quantity}]
     */
    public function executeWithLocations(Order $order, array $shipmentData): void
    {
        if ($order->shipments()->exists()) {
            throw new \RuntimeException('Đơn hàng đã được điều phối vận chuyển.');
        }

        if (! $order->shipping_method_id) {
            throw new \RuntimeException('Đơn hàng không cần vận chuyển.');
        }

        // COD orders can ship without payment (collect on delivery)
        // Prepaid orders must be paid first
        $paymentMethod = is_string($order->payment_method)
            ? PaymentMethod::tryFrom($order->payment_method)
            : $order->payment_method;

        if ($paymentMethod && $paymentMethod->isPrepaid() && ! $order->paid_at) {
            throw new \RuntimeException('Đơn hàng chưa được thanh toán, chưa tạo vận chuyển.');
        }

        DB::transaction(function () use ($order, $shipmentData) {
            // Group shipment data by location
            $itemsByLocation = [];

            foreach ($shipmentData as $data) {
                $locId = $data['location_id'];
                if (! isset($itemsByLocation[$locId])) {
                    $itemsByLocation[$locId] = [];
                }
                $itemsByLocation[$locId][] = $data;
            }

            // Create one shipment per unique location
            foreach ($itemsByLocation as $locId => $items) {
                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'shipment_number' => Shipment::generateShipmentNumber(),
                    'origin_location_id' => $locId,
                    'shipping_method_id' => $order->shipping_method_id,
                    'status' => ShipmentStatus::Pending,
                ]);

                foreach ($items as $item) {
                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'order_item_id' => $item['order_item_id'],
                        'variant_id' => $item['variant_id'] ?? null,
                        'quantity_shipped' => $item['quantity'],
                    ]);
                }
            }
        });
    }
}
