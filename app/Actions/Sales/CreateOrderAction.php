<?php

namespace App\Actions\Sales;

use App\Data\Sales\CreateOrderData;
use App\Enums\OrderStatus;
use App\Models\Auth\User;
use App\Models\Customer\CustomerAddress;
use App\Models\Product\Bundle;
use App\Models\Product\Product;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(CreateOrderData $data): Order
    {
        DB::beginTransaction();

        try {
            $user = User::where('type', 'customer')->findOrFail($data->customer_id);
            $customerProfile = $user->customer;

            if (! $customerProfile) {
                throw new \RuntimeException('Khách hàng chưa có thông tin.');
            }

            $address = CustomerAddress::where('customer_id', $customerProfile->id)
                ->findOrFail($data->shipping_address_id);

            $validatedItems = $this->validateItems($data->items);
            $totalAmount = collect($validatedItems)->sum(fn($item) => $item['unit_price'] * $item['quantity']);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $user->id,
                'shipping_address_id' => $address->id,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
            ]);

            foreach ($validatedItems as $itemData) {
                $order->items()->create($itemData);
            }

            DB::commit();

            return $order->load(['items', 'customer', 'shippingAddress']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    protected function validateItems(array $items): array
    {
        $validated = [];

        foreach ($items as $item) {
            $purchasable = $this->resolvePurchasable(
                $item['purchasable_type'],
                $item['purchasable_id']
            );

            if (! $purchasable || ($purchasable instanceof Product && $purchasable->status->value === 'archived')) {
                throw new \RuntimeException('Sản phẩm không khả dụng.');
            }

            $validated[] = [
                'purchasable_type' => $item['purchasable_type'],
                'purchasable_id' => $item['purchasable_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'configuration' => $item['configuration'] ?? [],
            ];
        }

        return $validated;
    }

    protected function resolvePurchasable(string $type, string $id): mixed
    {
        return match ($type) {
            Product::class => Product::find($id),
            Bundle::class => Bundle::find($id),
            default => throw new \InvalidArgumentException('Invalid purchasable type.'),
        };
    }
}
