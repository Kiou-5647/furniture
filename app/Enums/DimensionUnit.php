<?php

namespace App\Enums;

enum DimensionUnit: string
{
    case Milimeter = 'mm';
    case Centimeter = 'cm';
    case Meter = 'm';
    case Inch = 'inch';
    case Feet = 'ft';

    public function label(): string
    {
        return match ($this) {
            self::Milimeter => 'Milimét',
            self::Centimeter => 'Centimét',
            self::Meter => 'Mét',
            self::Inch => 'Inch',
            self::Feet => 'Feet',
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
