<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public float $freeship_threshold;
    public int $default_warranty;

    public static function group(): string
    {
        return 'general';
    }

    public static function labels(): array
    {
        return [
            'site_name' => 'Tên trang web',
            'freeship_threshold' => 'Ngưỡng miễn phí vận chuyển',
            'default_warranty' => 'Bảo hành mặc định',
        ];
    }
}
