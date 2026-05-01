<?php

namespace App\Enums;

enum UserType: string
{
    case Employee = 'employee';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Nhân viên',
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
