<?php

namespace App\Actions\Fulfillment;

use App\Models\Sales\Order;
use App\Services\Fulfillment\FulfillmentRouterService;

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

        if (! $order->paid_at) {
            throw new \RuntimeException('Đơn hàng chưa được thanh toán, chưa tạo vận chuyển.');
        }

        if (! $order->shipping_method_id) {
            throw new \RuntimeException('Đơn hàng không cần vận chuyển.');
        }

        $this->router->routeOrder($order);
    }
}
