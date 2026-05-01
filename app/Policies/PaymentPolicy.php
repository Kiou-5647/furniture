<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Xem thanh toán');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('Xem thanh toán');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý thanh toán');
    }

    public function manage(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('Quản lý thanh toán');
    }
}
