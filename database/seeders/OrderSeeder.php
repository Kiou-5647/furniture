<?php

namespace Database\Seeders;

use App\Actions\Sales\CancelOrderAction;
use App\Actions\Sales\CreateOrderAction;
use App\Actions\Sales\ProcessPaymentAction;
use App\Actions\Fulfillment\ShipShipmentAction;
use App\Actions\Fulfillment\DeliverShipmentAction;
use App\Actions\Fulfillment\ReturnShipmentItemAction;
use App\Data\Sales\CreateOrderData;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Fulfillment\ShippingMethod;
use App\Models\Hr\Employee;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Product\ProductVariant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = \App\Models\Customer\Customer::all();
        $employees = Employee::all();
        $variants = ProductVariant::all();
        $locations = Location::all();
        $shippingMethods = ShippingMethod::all();

        if ($customers->isEmpty() || $variants->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Required data missing. Please seed Customers, ProductVariants, and Employees first.');
            return;
        }

        $this->command->info('Starting high-fidelity order simulation (JIT Stocking)...');

        // Simulation expanded to 60 days
        for ($day = 60; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            $isToday = ($day === 0);

            // Order count: 0-10 for past days, exactly 5 for today
            $count = $isToday ? 5 : rand(0, 10);

            $this->command->info("Simulating orders for {$date->format('Y-m-d')} ({$count} orders)...");

            for ($i = 0; $i < $count; $i++) {
                // Scenario transition moved to 5 days
                if ($day > 5) {
                    $scenario = collect(['completed', 'cancelled'])->random();
                } else {
                    $scenario = collect(['completed', 'processing', 'pending', 'cancelled'])->random();
                }

                $this->simulateOrderLifecycle($scenario, $date, $customers, $employees, $variants, $locations, $shippingMethods);
            }
        }

        // POST-SIMULATION: Finalize all refund requests based on payment dates
        $this->command->info('Finalizing all refund requests...');
        $refunds = \App\Models\Sales\Refund::where('status', '!=', \App\Enums\RefundStatus::Completed)->get();
        $processRefundAction = app(\App\Actions\Sales\ProcessRefundAction::class);
        $employee = $employees->random();

        foreach ($refunds as $refund) {
            $order = $refund->order;
            // Process the refund as completed
            $processRefundAction->approve($refund, $employee);

            // Set processed_at close to the order's paid_at date (1-3 days after)
            if ($order && $order->paid_at) {
                $processedAt = $order->paid_at->copy()->addDays(rand(1, 3))->addHours(rand(1, 23));
                if ($processedAt->isFuture()) {
                    $processedAt = Carbon::now();
                }
                $refund->update([
                    'processed_at' => $processedAt
                ]);
            } else {
                $refund->update(['processed_at' => Carbon::now()]);
            }
        }

        $this->command->info('Simulation complete!');
    }

    protected function simulateOrderLifecycle($scenario, Carbon $date, $customers, $employees, $variants, $locations, $shippingMethods)
    {
        $customer = $customers->random();
        $employee = $employees->random();
        $location = $locations->random();
        $shippingMethod = $shippingMethods->random();

        // 1. Determine items first to know how much stock to inject
        $items = [];
        $itemsCount = rand(1, 3);
        for ($j = 0; $j < $itemsCount; $j++) {
            $variant = $variants->random();
            $qty = rand(1, 2);
            $items[] = [
                'purchasable_type' => get_class($variant),
                'purchasable_id' => $variant->id,
                'quantity' => $qty,
                'unit_price' => (float) $variant->price,
            ];
        }

        // JUST-IN-TIME STOCKING: Inject exactly what is needed for this order
        foreach ($items as $item) {
            $inventory = Inventory::firstOrCreate(
                ['variant_id' => $item['purchasable_id'], 'location_id' => $location->id],
                ['quantity' => 0]
            );

            $inventory->increment('quantity', $item['quantity']);
        }

        // 2. CREATE ORDER using Action
        $province = \App\Models\Setting\Province::inRandomOrder()->first();
        $ward = \App\Models\Setting\Ward::where('province_code', $province->province_code)->inRandomOrder()->first();

        $createOrderData = new CreateOrderData(
            customer_id: $customer->id,
            items: $items,
            source: 'online',
            store_location_id: $location->id,
            shipping_method_id: $shippingMethod->id,
            payment_method: collect([PaymentMethod::Cash, PaymentMethod::BankTransfer, PaymentMethod::Cod])->random()->value,
            province_code: $province->province_code,
            ward_code: $ward->ward_code,
            province_name: $province->name,
            ward_name: $ward->name,
            street: fake()->streetAddress(),
            shipping_cost: 30000,
            guest_name: $customer->name,
            guest_phone: $customer->phone,
            guest_email: $customer->email,
        );

        $order = app(CreateOrderAction::class)->execute($createOrderData);

        // Backdate the order and record handling employee
        $order->update([
            'created_at' => $date,
            'accepted_by' => $employee->id,
        ]);

        if ($scenario === 'pending') {
            return;
        }

        // 3. PROCESS PAYMENT using Action
        $invoice = $order->invoices()->first();
        if (!$invoice) return;

        $gateway = $order->payment_method === PaymentMethod::BankTransfer ? 'vnpay' : 'cash';

        $paymentData = [
            'customer_id' => $customer->id,
            'gateway' =>  $gateway,
            'transaction_id' => 'PAY-' . $date->format('YmdHis') . '-' . rand(1000, 9999),
            'amount' => (float) $invoice->amount_due,
            'allocations' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount' => (float) $invoice->amount_due,
                ],
            ],
        ];

        app(ProcessPaymentAction::class)->execute($paymentData);

        // Distribute payment date naturally (0-3 days after order creation)
        $paymentDate = $date->copy()->addDays(rand(0, 3))->addHours(rand(1, 23));
        if ($paymentDate->isFuture()) {
            $paymentDate = Carbon::now();
        }

        // Backdate the order payment and the payment records in DB
        $order->update(['paid_at' => $paymentDate]);

        // Find the payment just created to backdate it and its allocations
        $lastPayment = \App\Models\Sales\Payment::latest()->first();
        if ($lastPayment) {
            $lastPayment->update(['created_at' => $paymentDate]);
            $lastPayment->allocations()->update(['created_at' => $paymentDate]);
        }

        if ($scenario === 'cancelled' && rand(1, 10) > 7) {
            app(CancelOrderAction::class)->execute($order, $employee);
            return;
        }

        if ($scenario === 'processing') {
            return;
        }

        // 4. SHIPPING & DELIVERY using Actions
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'shipment_number' => Shipment::generateShipmentNumber(),
            'origin_location_id' => $location->id,
            'shipping_method_id' => $shippingMethod->id,
            'status' => ShipmentStatus::Pending,
            'handled_by' => $employee->id,
            'created_at' => $date->copy()->addHours(rand(1, 12)),
        ]);

        foreach ($order->items as $item) {
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_item_id' => $item->id,
                'variant_id' => $item->purchasable_id,
                'quantity_shipped' => $item->quantity,
                'status' => ShipmentStatus::Pending,
            ]);
        }

        // Move to Shipped
        app(ShipShipmentAction::class)->execute($shipment, $employee);

        // Randomly decide whether to deliver, return, or cancel shipment
        $deliveryRoll = rand(1, 100);
        if ($deliveryRoll <= 70) {
            // Fully Deliver
            app(DeliverShipmentAction::class)->execute($shipment, $employee);
        } elseif ($deliveryRoll <= 85) {
            // Partially return some items
            $items = $shipment->items;
            foreach ($items as $shipItem) {
                if (rand(1, 10) > 7) {
                    app(ReturnShipmentItemAction::class)->execute($shipItem, 'Khách hàng không hài lòng', $employee);
                }
            }
            // Still mark as delivered for the rest
            app(DeliverShipmentAction::class)->execute($shipment, $employee);
        } elseif ($deliveryRoll <= 95) {
            // Cancel Shipment - New Scenario
            app(\App\Actions\Fulfillment\CancelShipmentAction::class)->execute($shipment, $employee);
        }
    }
}
