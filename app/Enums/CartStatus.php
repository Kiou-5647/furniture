<?php

namespace App\Enums;

enum CartStatus: string
{
    case Open = 'open';
    case CheckedOut = 'checked_out';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Đang mở',
            self::CheckedOut => 'Đã thanh toán',
            self::Abandoned => 'Đã bỏ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'green',
            self::CheckedOut => 'blue',
            self::Abandoned => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
        ])->toArray();
    }
}
