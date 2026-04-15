<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;

class ShipmentPolicy
{
    public function ship(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.ship');
    }

    public function deliver(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.deliver');
    }

    public function cancel(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.cancel');
    }

    public function resend(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.resend');
    }

    public function returnItem(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.return_item');
    }

    public function updateLocation(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.update_location');
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.delete');
    }

    public function restore(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.restore');
    }

    public function forceDelete(User $user, Shipment $shipment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('shipments.force_delete');
    }
}
