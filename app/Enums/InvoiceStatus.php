<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::Open => 'Đang mở',
            self::Paid => 'Đã thanh toán',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Open => 'yellow',
            self::Paid => 'green',
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
