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

        $this->router->routeOrder($order);
    }
}
