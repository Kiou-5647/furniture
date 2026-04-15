<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Payment;

class PaymentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('payments.create');
    }

    public function manage(User $user, Payment $payment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('payments.manage');
    }
}
