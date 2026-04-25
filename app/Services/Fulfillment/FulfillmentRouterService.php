<?php

namespace App\Services\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Product\Bundle;
use App\Models\Sales\Order;
use App\Services\Location\StockLocatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FulfillmentRouterService
{
    public function __construct(
        protected StockLocatorService $stockLocator,
    ) {}

    public function routeOrder(Order $order): void
    {
        if ($order->shipments()->exists()) {
            return;
        }

        Log::info('Start routing for location');

        DB::transaction(function () use ($order) {
            $itemsByLocation = [];

            foreach ($order->items as $item) {
                if ($item->purchasable_type === 'App\\Models\\Product\\Bundle') {
                    $this->handleBundleItem($item, $order, $itemsByLocation);
                    continue;
                }

                // Handle standard ProductVariant items
                $locationId = $item->source_location_id
                    ?? $this->resolveBestLocation($item->purchasable_id)
                    ?? $order->store_location_id;

                if ($locationId) {
                    $itemsByLocation[$locationId][] = [
                        'order_item' => $item,
                        'quantity' => $item->quantity,
                    ];
                }
            }

            Log::info($itemsByLocation);

            foreach ($itemsByLocation as $locationId => $locationItems) {
                $this->createShipment($order, $locationId, $locationItems);
            }
        });

        Log::info('End routing for location');
    }

    protected function handleBundleItem($item, Order $order, array &$itemsByLocation): void
    {
        $bundle = Bundle::find($item->purchasable_id);
        $config = $item->configuration;

        if (!$bundle || !is_array($config)) {
            return;
        }

        foreach ($bundle->contents as $content) {
            $variantId = $config[$content->id] ?? null;

            if ($variantId) {
                // Resolve the best location for this specific variant in the bundle
                $locationId = $this->resolveBestLocation($variantId)
                    ?? $order->store_location_id;

                if ($locationId) {
                    $itemsByLocation[$locationId][] = [
                        'order_item' => $item, // Link back to the Bundle OrderItem
                        'variant_id' => $variantId,
                        'quantity' => $content->quantity * $item->quantity,
                    ];
                }
            }
        }
    }

    protected function resolveBestLocation(string $variantId): ?string
    {
        $stockOptions = $this->stockLocator->findStockForItem(
            'App\\Models\\Product\\ProductVariant',
            $variantId
        );

        return $stockOptions->isNotEmpty()
            ? $stockOptions->sortByDesc('available_qty')->first()['location_id']
            : null;
    }

    protected function createShipment(Order $order, string $locationId, array $items): Shipment
    {
        Log::info('Start create shipment for location');
        DB::beginTransaction();
        try {
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'shipment_number' => Shipment::generateShipmentNumber(),
                'origin_location_id' => $locationId,
                'status' => ShipmentStatus::Pending,
            ]);

            foreach ($items as $item) {
                $orderItemId = $item['order_item']->id;

                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'order_item_id' => $orderItemId,
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity_shipped' => $item['quantity'],
                ]);
            }
        } catch (RuntimeException $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }


        return $shipment;
    }
}
