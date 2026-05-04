<?php

namespace Database\Factories\Sales;

use App\Models\Auth\User;
use App\Models\Sales\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory()->state(['type' => 'customer']),
            'order_number' => 'ORD-' . now()->format('dmy') . '-' . strtoupper(\Str::random(8)),
            'total_amount' => fake()->randomFloat(2, 100000, 1000000),
            'total_items' => fake()->numberBetween(1, 5),
            'shipping_cost' => fake()->randomFloat(2, 0, 50000),
            'status' => OrderStatus::Processing,
            'payment_method' => PaymentMethod::Cod,
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
