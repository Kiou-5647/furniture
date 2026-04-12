<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cod = 'cod';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tiền mặt',
            self::BankTransfer => 'Chuyển khoản',
            self::Cod => 'Thu hộ (COD)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cash => 'green',
            self::BankTransfer => 'blue',
            self::Cod => 'yellow',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function isPrepaid(): bool
    {
        return in_array($this, [self::Cash, self::BankTransfer], true);
    }
}
