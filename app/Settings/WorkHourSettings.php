<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WorkHourSettings extends Settings
{
    public int $morning_start;
    public int $morning_end;
    public int $afternoon_start;
    public int $afternoon_end;

    public static function group(): string
    {
        return 'work_hours';
    }

    public static function labels(): array
    {
        return [
            'morning_start' => 'Bắt đầu',
            'morning_end' => 'Kết thúc',
            'afternoon_start' => 'Bắt đầu',
            'afternoon_end' => 'Kết thúc',
        ];
    }
}
