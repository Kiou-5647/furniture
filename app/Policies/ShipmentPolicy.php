<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;

class ShipmentPolicy
{
    public function view(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.view');
    }

    public function update(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasPermissionTo('shipments.update');
    }

    public function forceDelete(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! $user->hasPermissionTo('shipments.force_delete')) {
            return false;
        }

        return $user->isEmployee();
    }
}
