<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Refund;

class RefundPolicy
{
    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('refunds.approve');
    }

    public function reject(User $user, Refund $refund): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('refunds.reject');
    }

    public function process(User $user, Refund $refund): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('refunds.process');
    }
}
