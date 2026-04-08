<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\GeodataService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GeodataController
{
    public function __construct(private GeodataService $service) {}

    public function provinces(): Collection
    {
        return $this->service->getProvinces();
    }

    public function wards(Request $request): Collection|array
    {
        $provinceCode = $request->query('province_code');

        if (! $provinceCode) {
            return [];
        }

        return $this->service->getWards($provinceCode);
    }
}
