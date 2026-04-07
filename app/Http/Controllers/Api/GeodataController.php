<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting\Province;
use App\Models\Setting\Ward;
use Illuminate\Http\Request;

class GeodataController
{
    public function provinces()
    {
        return Province::query()
            ->orderBy('name')
            ->get(['province_code as value', 'name as label', 'short_name'])
            ->map(fn ($province) => [
                'value' => $province->value,
                'label' => $province->short_name ?? $province->label,
            ]);
    }

    public function wards(Request $request)
    {
        $provinceCode = $request->query('province_code');

        if (! $provinceCode) {
            return [];
        }

        return Ward::query()
            ->where('province_code', $provinceCode)
            ->orderBy('name')
            ->get(['ward_code as value', 'name as label'])
            ->map(fn ($ward) => [
                'value' => $ward->value,
                'label' => $ward->label,
            ]);
    }
}
