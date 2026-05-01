<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;

class ShipmentPolicy
{
    public function ship(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }

    public function deliver(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }

    public function cancel(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }

    public function resend(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }

    public function returnItem(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }

    public function updateLocation(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('Quản lý vận chuyển');
    }
}
