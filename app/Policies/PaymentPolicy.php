<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Payment;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('payments.view');
    }

    public function update(User $user, Payment $payment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('accountant')) {
            return true;
        }

        return $user->hasPermissionTo('payments.update');
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('payments.force_delete');
    }
}
