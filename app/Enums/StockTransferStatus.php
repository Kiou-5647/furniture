<?php

namespace App\Enums;

enum StockTransferStatus: string
{
    case Draft = 'draft';
    case InTransit = 'in_transit';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::InTransit => 'Đang vận chuyển',
            self::Received => 'Đã nhận',
            self::Cancelled => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InTransit => 'yellow',
            self::Received => 'green',
            self::Cancelled => 'red',
        };
    }

    public function canBeShipped(): bool
    {
        return $this === self::Draft;
    }

    public function canBeReceived(): bool
    {
        return $this === self::InTransit;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Draft, self::InTransit], true);
    }
}
