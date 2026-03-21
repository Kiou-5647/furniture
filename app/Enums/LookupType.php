<?php

namespace App\Enums;

enum LookupType: string
{
    case Rooms = 'phong';
    case Styles = 'phong-cach';
    case Lifestyles = 'loi-song';
    case Colors = 'mau-sac';
    case Features = 'tinh-nang';
    case Shape = 'hinh-dang';
    case Size = 'kich-co';
    case Pattern = 'hoa-van';
    case CategoryGroup = 'nhom-danh-muc';

    public function label(): string
    {
        return match ($this) {
            self::Rooms => 'Phòng',
            self::Styles => 'Phong cách',
            self::Lifestyles => 'Lối sống',
            self::Colors => 'Màu sắc',
            self::Features => 'Tính năng',
            self::Shape => 'Hình dáng',
            self::Size => 'Kích cỡ',
            self::Pattern => 'Hoa văn',
            self::CategoryGroup => 'Nhóm danh mục',
        };
    }
}
