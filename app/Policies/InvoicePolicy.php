<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Sales\Invoice;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('invoices.view');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('accountant')) {
            return true;
        }

        return $user->hasPermissionTo('invoices.update');
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('invoices.force_delete');
    }
}
