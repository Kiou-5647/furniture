<?php

namespace App\Enums;

enum ProductType: string
{
    case Furniture = 'noi-that';
    case Decoration = 'trang-tri';

    public function label(): string
    {
        return match ($this) {
            self::Furniture => 'Nội thất',
            self::Decoration => 'Trang trí',
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
