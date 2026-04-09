<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PendingDeposit = 'pending_deposit';
    case PendingConfirmation = 'pending_confirmation';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingDeposit => 'Chờ đặt cọc',
            self::PendingConfirmation => 'Chờ xác nhận',
            self::Confirmed => 'Đã xác nhận',
            self::Completed => 'Hoàn thành',
            self::Cancelled => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingDeposit => 'yellow',
            self::PendingConfirmation => 'orange',
            self::Confirmed => 'blue',
            self::Completed => 'green',
            self::Cancelled => 'gray',
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
