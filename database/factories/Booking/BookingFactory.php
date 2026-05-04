<?php

namespace Database\Factories\Booking;

use App\Enums\BookingStatus;
use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Hr\Designer;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'designer_id' => Designer::factory(),
            'start_at' => now()->addDays(rand(1, 30)),
            'end_at' => now()->addDays(rand(31, 60)),
            'status' => $this->faker->randomElement(BookingStatus::cases()),
            'total_price' => $this->faker->randomFloat(2, 100, 1000),
            'notes' => $this->faker->sentence(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'province_code' => null,
            'ward_code' => null,
            'province_name' => 'Hà Nội',
            'ward_name' => 'Hoàn Kiếm',
            'street' => 'Đường ABC',
            'booking_number' => 'BKG-' . now()->format('dmy') . '-' . strtoupper($this->faker->bothify('??##??##')),
        ];
    }
}
