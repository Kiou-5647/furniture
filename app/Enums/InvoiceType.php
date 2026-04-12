<?php

namespace App\Enums;

enum InvoiceType: string
{
    case Deposit = 'deposit';
    case FinalBalance = 'final_balance';
    case Full = 'full';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Đặt cọc',
            self::FinalBalance => 'Thanh toán còn lại',
            self::Full => 'Thanh toán toàn bộ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Deposit => 'orange',
            self::FinalBalance => 'blue',
            self::Full => 'green',
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
