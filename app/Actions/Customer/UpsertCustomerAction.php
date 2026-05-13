<?php

namespace App\Actions\Customer;

use App\Data\Customer\CreateCustomerData;
use App\Models\Auth\User;
use App\Models\Customer\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpsertCustomerAction
{
    public function execute(CreateCustomerData $data, ?Customer $customer = null): Customer
    {
        return DB::transaction(function () use ($data, $customer) {
            if ($customer) {
                // Update existing customer and their user account
                $user = $customer->user;
                $user->update([
                    'name' => $data->full_name,
                    'email' => $data->email,
                ]);

                $customer->update($data->toArray());
                return $customer;
            }

            // Create new customer and their user account
            $user = User::create([
                'name' => $data->full_name,
                'email' => $data->email,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            return Customer::create($data->toArray() + ['user_id' => $user->id]);
        });
    }
}
