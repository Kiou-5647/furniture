<?php

namespace App\Services\Location;

use App\Models\Inventory\Location;
use Illuminate\Support\Collection;

class LocationDistanceService
{
    /**
     * Find locations sorted by proximity to customer's address.
     *
     * Priority:
     * 1. Same province (province_code matches)
     * 2. Other locations (no distance data available)
     */
    public function getClosestLocationsToAddress(
        ?string $provinceCode,
        ?string $locationType = null
    ): Collection {
        $query = Location::query()
            ->where('is_active', true)
            ->with(['manager']);

        if ($locationType) {
            $query->where('type', $locationType);
        }

        return $query->get()->sortBy(function (Location $location) use ($provinceCode) {
            // Same province = closest (priority 0)
            if ($provinceCode && $location->province_code === $provinceCode) {
                return 0;
            }

            // Different province = farther (priority 1)
            return 1;
        })->values();
    }

    /**
     * Find the single closest location to customer's address.
     */
    public function getClosestLocationToAddress(
        ?string $provinceCode,
        ?string $locationType = null
    ): ?Location {
        return $this->getClosestLocationsToAddress($provinceCode, $locationType)->first();
    }
}
