<?php

namespace App\Enums;

enum DesignServiceType: string
{
    case Consultation = 'consultation';
    case CustomBuild = 'custom_build';

    public function label(): string
    {
        return match ($this) {
            self::Consultation => 'Tư vấn',
            self::CustomBuild => 'Thiết kế theo yêu cầu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Consultation => 'blue',
            self::CustomBuild => 'purple',
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
