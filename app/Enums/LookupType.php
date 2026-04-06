<?php

namespace App\Enums;

enum LookupType: string
{
    case CategoryGroup = 'nhom-danh-muc';
    case Rooms = 'phong';
    case Styles = 'phong-cach';
    case Lifestyles = 'loi-song';
    case Colors = 'mau-sac';
    case Features = 'tinh-nang';
    case Shape = 'hinh-dang';
    case Size = 'kich-co';
    case Pattern = 'hoa-van';
    case Materials = 'chat-lieu';
    case DesignType = 'loai-thiet-ke';
    case Finish = 'hoan-thien';
    case BaseType = 'loai-de';

    public function label(): string
    {
        return match ($this) {
            self::CategoryGroup => 'Nhóm danh mục',
            self::Rooms => 'Phòng',
            self::Styles => 'Phong cách',
            self::Lifestyles => 'Lối sống',
            self::Colors => 'Màu sắc',
            self::Features => 'Tính năng',
            self::Shape => 'Hình dáng',
            self::Size => 'Kích cỡ',
            self::Pattern => 'Hoa văn',
            self::Materials => 'Chất liệu',
            self::DesignType => 'Loại thiết kế',
            self::Finish => 'Hoàn thiện',
            self::BaseType => 'Loại đế',
        };
    }

    public function forVariants(): bool
    {
        return match ($this) {
            self::Colors,
            self::Size,
            self::Materials,
            self::Finish,
            self::Shape,
            self::Pattern,
            self::BaseType => true,
            default => false,
        };
    }
}
