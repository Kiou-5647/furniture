<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PAYMENT['SELECT']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo(Permission::PAYMENT['SELECT']);
    }
}
