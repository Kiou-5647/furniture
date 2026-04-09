<?php

namespace App\Actions\Fortify;

use App\Models\Auth\User;
use App\Models\Customer\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomerProfile
{
    /**
     * Create a customer profile for the given user.
     */
    public function create(User $user, array $input = []): Customer
    {
        return DB::transaction(function () use ($user, $input) {
            return $user->customer()->create([
                'full_name' => $input['name'] ?? $user->name,
                'phone' => $input['phone'] ?? null,
                'total_spent' => 0.00,
                'loyalty_points' => 0,
            ]);
        });
    }
}
