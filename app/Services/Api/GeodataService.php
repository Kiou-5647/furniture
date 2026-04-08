<?php

namespace App\Services\Api;

use App\Models\Setting\Province;
use App\Models\Setting\Ward;
use App\Support\CacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GeodataService
{
    public function getProvinces(): Collection
    {
        return Cache::remember(
            CacheKeys::geodata('provinces'),
            CacheKeys::TTL,
            fn () => Province::query()
                ->orderBy('name')
                ->get(['province_code as value', 'name as label', 'short_name'])
                ->map(fn ($province) => [
                    'value' => $province->value,
                    'label' => $province->short_name ?? $province->label,
                ])
        );
    }

    public function getWards(string $provinceCode): Collection
    {
        return Cache::remember(
            CacheKeys::geodata("wards.{$provinceCode}"),
            CacheKeys::TTL,
            fn () => Ward::query()
                ->where('province_code', $provinceCode)
                ->orderBy('name')
                ->get(['ward_code as value', 'name as label'])
                ->map(fn ($ward) => [
                    'value' => $ward->value,
                    'label' => $ward->label,
                ])
        );
    }
}
