<?php

namespace App\Enums;

enum LocationType: string
{
    case Warehouse = 'warehouse';
    case Retail = 'retail';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Warehouse => 'Kho hàng',
            self::Retail => 'Cửa hàng',
            self::Vendor => 'Nhà cung cấp',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Warehouse => 'blue',
            self::Retail => 'green',
            self::Vendor => 'orange',
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
