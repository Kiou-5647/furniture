<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Customer\Customer;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CUSTOMER['SELECT']);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(Permission::CUSTOMER['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CUSTOMER['CREATE']);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(Permission::CUSTOMER['UPDATE']);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(Permission::CUSTOMER['DELETE']);
    }
}
