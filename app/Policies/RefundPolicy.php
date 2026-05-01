<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Refund;

class RefundPolicy
{
    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo('Quản lý hoàn tiền');
    }

    public function reject(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo('Quản lý hoàn tiền');
    }

    public function process(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo('Quản lý hoàn tiền');
    }
}
