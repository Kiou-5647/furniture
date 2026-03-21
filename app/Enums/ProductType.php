<?php

namespace App\Enums;

enum ProductType: string
{
    case Furniture = 'noi-that';
    case Accessory = 'phu-kien';
    case Decoration = 'trang-tri';
    case Lighting = 'thap-sang';

    public function label(): string
    {
        return match ($this) {
            self::Furniture => 'Nội thất',
            self::Accessory => 'Phụ kiện',
            self::Decoration => 'Trang trí',
            self::Lighting => 'Thắp sáng',
        };
    }
}
