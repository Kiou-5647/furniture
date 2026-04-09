<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;

class ShipmentPolicy
{
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->isEmployee() && $user->hasPermissionTo('shipments.view')) {
            return true;
        }

        if ($user->isVendor()) {
            return $shipment->vendor_id === $user->vendor?->id;
        }

        return false;
    }

    public function update(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! $user->hasPermissionTo('shipments.update')) {
            return false;
        }

        if ($user->isVendor()) {
            return $shipment->vendor_id === $user->vendor?->id;
        }

        return true;
    }

    public function forceDelete(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! $user->hasPermissionTo('shipments.force_delete')) {
            return false;
        }

        // Only employees can force delete shipments
        return $user->isEmployee();
    }
}
