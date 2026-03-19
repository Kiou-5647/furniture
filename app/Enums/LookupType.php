<?php

namespace App\Enums;

enum LookupType: string
{
    case Colors = 'mau-sac';
    case Rooms = 'phong';
    case Styles = 'phong-cach';
    case Features = 'tinh-nang';

    public function label(): string
    {
        return match ($this) {
            self::Colors => 'Màu sắc',
            self::Rooms => 'Phòng',
            self::Styles => 'Phong cách',
            self::Features => 'Tính năng',
        };
    }
}
