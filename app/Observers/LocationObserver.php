<?php

namespace App\Observers;

use App\Models\Inventory\Location;

class LocationObserver
{
    public function creating(Location $location): void
    {
        if (empty($location->code)) {
            $location->code = $location->generateCode($location->type->value ?? 'warehouse');
        }
    }

    public function deleting(Location $location): void
    {
        $location->inventories()->delete();
    }
}
