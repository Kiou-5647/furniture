<?php

namespace App\Actions\Customer;

use App\Models\Customer\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Request;

class UpdateCustomerAction
{
    public function execute(Customer $customer, array $data)
    {
        $avatarFile = $data['avatar'] ?? null;
        $avatarUrl = $data['avatar_url'] ?? null;
        $street = $data['street'] ?? null;
        unset($data['avatar'], $data['avatar_url'], $data['street']);

        $userData = [];
        $customerData = [];

        if (isset($data['name'])) {
            $userData['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] !== $customer->user->email) {
            $userData['email'] = $data['email'];
            $userData['email_verified_at'] = null;
        }

        if (isset($data['is_active'])) {
            $userData['is_active'] = (bool) $data['is_active'];
        }

        foreach (
            [
                'full_name',
                'phone',
                'provice_code',
                'provice_name',
                'ward_code',
                'ward_name'
            ] as $key
        ) {
            if (isset($data[$key])) {
                $customerData[$key] = $data[$key];
            }
        }

        if (! empty($userData)) {
            $customer->user->update($userData);
        }

        if (! empty($customerData)) {
            $customer->update($customerData);
        }

        if ($avatarFile instanceof UploadedFile) {
            $customer->clearMediaCollection('avatar');
            $customer->addMedia($avatarFile)->toMediaCollection('avatar');
        }

        if (!$avatarUrl && !$avatarFile) {
            $customer->clearMediaCollection('avatar');
        }

        return $customer->fresh(['user']);
    }
}
