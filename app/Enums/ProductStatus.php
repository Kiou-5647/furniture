<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Hidden = 'hidden';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bản nháp',
            self::PendingReview => 'Chờ duyệt',
            self::Published => 'Đã xuất bản',
            self::Hidden => 'Đang ẩn',
            self::Archived => 'Đã lưu trữ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingReview => 'yellow',
            self::Published => 'green',
            self::Hidden => 'orange',
            self::Archived => 'slate',
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
