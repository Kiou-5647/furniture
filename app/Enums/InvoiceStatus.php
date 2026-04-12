<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Overpaid = 'overpaid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::Open => 'Đang mở',
            self::Paid => 'Đã thanh toán',
            self::Overpaid => 'Thanh toán thừa',
            self::Cancelled => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Open => 'yellow',
            self::Paid => 'green',
            self::Overpaid => 'blue',
            self::Cancelled => 'red',
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
