<?php

namespace App\Enums;

enum AttributeType: string
{
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Color = 'color';
    case Dimensions = 'dimensions';
    case Weight = 'weight';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Văn bản',
            self::Number => 'Số',
            self::Boolean => 'Boolean',
            self::Color => 'Màu sắc',
            self::Dimensions => 'Kích thước',
            self::Weight => 'Cân nặng',
        };
    }
}
