<?php

namespace App\Enums;

enum WeightUnit: string
{
    case Kilogram = 'kg';
    case Pound = 'lb';

    public function label(): string
    {
        return match ($this) {
            self::Kilogram => 'Kilogram',
            self::Pound => 'Pound',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
