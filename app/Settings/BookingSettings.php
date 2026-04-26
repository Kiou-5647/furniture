<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BookingSettings extends Settings
{
    public float $deposit_percentage;

    public static function group(): string
    {
        return 'bookings';
    }

    public static function labels(): array
    {
        return [
            'deposit_percentage' => 'Mức đặt cọc',
        ];
    }
}
