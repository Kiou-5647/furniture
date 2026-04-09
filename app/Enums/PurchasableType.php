<?php

namespace App\Enums;

use App\Models\Product\Bundle;
use App\Models\Product\Product;

enum PurchasableType: string
{
    case Product = 'App\\Models\\Product\\Product';
    case Bundle = 'App\\Models\\Product\\Bundle';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Sản phẩm',
            self::Bundle => 'Gói sản phẩm',
        };
    }

    public function modelClass(): string
    {
        return match ($this) {
            self::Product => Product::class,
            self::Bundle => Bundle::class,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
