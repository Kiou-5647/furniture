<?php

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;

beforeEach(function () {
    // 1. Setup Product
    $this->product = Product::create([
        'name' => 'Metric Test Sofa',
        'status' => 'published',
        'option_groups' => [
            ['name' => 'Color', 'namespace' => 'color', 'is_swatches' => true, 'options' => [['value' => 'red', 'label' => 'Red']]],
        ],
    ]);

    // 2. Setup Variant
    $this->variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'METRIC-001',
        'name' => 'Red Sofa',
        'slug' => 'red-sofa',
        'price' => 1000000,
        'option_values' => ['color' => 'red'],
    ]);
});

it('increments sales count across the chain when shipment is delivered', function () {
    // Setup Order & Shipment
    $order = Order::create([
        'order_number' => 'ORD-TEST-001',
        'status' => OrderStatus::Processing,
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'purchasable_id' => $this->variant->id,
        'purchasable_type' => ProductVariant::class,
        'quantity' => 2,
        'unit_price' => 1000000,
    ]);

    $shipment = Shipment::create([
        'order_id' => $order->id,
        'shipment_number' => 'SHP-TEST-001', // --- ADDED ---
        'status' => ShipmentStatus::Pending,
    ]);
    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'variant_id' => $this->variant->id,
        'quantity_shipped' => 2,
        'status' => ShipmentStatus::Pending,
    ]);

    // Execution: Mark Order Completed and Shipment Delivered
    foreach ($shipment->items as $item) {
        $item->update(['status' => ShipmentStatus::Delivered]);
    }

    // 2. Mark order as completed
    $order->update(['status' => OrderStatus::Completed]);

    // 3. Finally, mark shipment as delivered to trigger the Observer
    $shipment->update(['status' => ShipmentStatus::Delivered]);

    // Refresh and Verify
    $this->variant->refresh();
    $this->product->refresh();
    $card = $this->variant->productCard;

    expect($this->variant->sales_count)->toBe(2)
        ->and($card->sales_count)->toBe(2)
        ->and($this->product->sales_count)->toBe(2);
});

it('decrements sales count when delivered shipment is returned', function () {
    // Setup: Already delivered
    $this->variant->update(['sales_count' => 5]);
    $this->product->update(['sales_count' => 5]);
    $card = $this->variant->productCard;
    $card->update(['sales_count' => 5]);

    $order = Order::create([
        'order_number' => 'ORD-TEST-002',
        'status' => OrderStatus::Completed,
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'purchasable_id' => $this->variant->id,
        'purchasable_type' => ProductVariant::class,
        'quantity' => 2,
        'unit_price' => 1000000,
    ]);
    $shipment = Shipment::create([
        'order_id' => $order->id,
        'shipment_number' => 'SHP-TEST-002', // --- ADDED ---
        'status' => ShipmentStatus::Delivered
    ]);
    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'variant_id' => $this->variant->id,
        'quantity_shipped' => 2,
        'status' => ShipmentStatus::Delivered,
    ]);

    // Execution: Mark as Returned
    foreach ($shipment->items as $item) {
        $item->update(['status' => ShipmentStatus::Returned]);
    }
    $shipment->update(['status' => ShipmentStatus::Returned]);

    $this->variant->refresh();
    $this->product->refresh();
    $card->refresh();

    expect($this->variant->sales_count)->toBe(3)
        ->and($card->sales_count)->toBe(3)
        ->and($this->product->sales_count)->toBe(3);
});

it('syncs aggregate metrics when variant popularity changes', function () {
    // Update variant metrics
    $this->variant->update([
        'views_count' => 100,
        'reviews_count' => 10,
        'average_rating' => 4.5,
    ]);

    $this->product->refresh();
    $card = $this->variant->productCard;
    $card->refresh();

    expect($this->product->views_count)->toBe(100)
        ->and($this->product->reviews_count)->toBe(10)
        ->and((float)$this->product->average_rating)->toBe(4.5)
        ->and($card->views_count)->toBe(100)
        ->and($card->reviews_count)->toBe(10)
        ->and((float)$card->average_rating)->toBe(4.5);
});
