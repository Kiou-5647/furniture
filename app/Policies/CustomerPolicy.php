<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Customer\Customer;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Xem khách hàng');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('Xem khách hàng');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('Quản lý khách hàng');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo('Quản lý khách hàng');
    }
}
