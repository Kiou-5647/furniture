<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Receive = 'receive';
    case Sell = 'sell';
    case Return = 'return';
    case Adjust = 'adjust';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Damage = 'damage';
    case Restock = 'restock';

    public function label(): string
    {
        return match ($this) {
            self::Receive => 'Nhập kho',
            self::Sell => 'Bán hàng',
            self::Return => 'Trả hàng',
            self::Adjust => 'Điều chỉnh',
            self::TransferIn => 'Nhận chuyển kho',
            self::TransferOut => 'Xuất chuyển kho',
            self::Damage => 'Hư hỏng',
            self::Restock => 'Nhập bổ sung',
        };
    }

    public function sampleNotes(): array
    {
        return match ($this) {
            self::Receive => ['Nhập bổ sung', 'Hàng trả về', 'Nhập từ nhà cung cấp'],
            self::Adjust => ['Kiểm kê định kỳ', 'Điều chỉnh sai sót', 'Sai lệch kho'],
            self::Damage => ['Hàng hỏng', 'Hàng hết hạn', 'Hư hỏng vận chuyển'],
            self::Sell => ['Bán lẻ', 'Bán sỉ', 'Đơn hàng website'],
            self::Return => ['Khách trả hàng', 'Hàng lỗi trả về'],
            self::TransferIn => ['Nhận từ kho khác'],
            self::TransferOut => ['Chuyển sang kho khác'],
            self::Restock => ['Nhập bổ sung tồn kho'],
        };
    }

    public function color(): string

    {
        return match ($this) {
            self::Receive, self::Restock, self::TransferIn, self::Return => 'green',
            self::Sell, self::TransferOut, self::Damage => 'red',
            self::Adjust => 'yellow',
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this, [self::Receive, self::Return, self::TransferIn, self::Restock], true);
    }

    public function isOutgoing(): bool
    {
        return in_array($this, [self::Sell, self::TransferOut, self::Damage], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
            'isIncoming' => $case->isIncoming(),
            'isOutgoing' => $case->isOutgoing(),
        ])->toArray();
    }
}
