<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Pending = 'pending';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Đang chờ',
            self::Shipped => 'Đã gửi',
            self::Delivered => 'Đã giao',
            self::Returned => 'Đã trả',
            self::Cancelled => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Shipped => 'blue',
            self::Delivered => 'green',
            self::Returned => 'orange',
            self::Cancelled => 'red',
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
