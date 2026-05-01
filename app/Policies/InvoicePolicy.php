<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Invoice;

class InvoicePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Quản lý hóa đơn');
    }

    public function manage(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('Quản lý hóa đơn');
    }
}
