<?php

namespace App\Enums;

enum BundleDiscountType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
    case FixedPrice = 'fixed_price';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Phần trăm',
            self::FixedAmount => 'Giảm trực tiếp',
            self::FixedPrice => 'Giá cố định',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Percentage => 'blue',
            self::FixedAmount => 'green',
            self::FixedPrice => 'purple',
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
