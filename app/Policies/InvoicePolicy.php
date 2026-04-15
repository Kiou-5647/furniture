<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Invoice;

class InvoicePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('invoices.create');
    }

    public function manage(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('invoices.manage');
    }
}
