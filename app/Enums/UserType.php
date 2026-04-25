<?php

namespace App\Enums;

enum UserType: string
{
    case Employee = 'employee';
    case Designer = 'designer';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Nhân viên',
            self::Designer => 'Nhà thiết kế',
            self::Customer => 'Khách hàng',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
