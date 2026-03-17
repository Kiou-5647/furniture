<?php

namespace App\Services\Vendor;

use App\Models\Auth\User;

class VendorVerificationService
{
    public function isVendorVerified(User $user): bool
    {
        $vendorUser = $user->vendorUser;

        if (! $vendorUser || ! $vendorUser->vendor_id) {
            return false;
        }

        $vendor = $vendorUser->vendor;

        return $vendor->verified_at !== null && $vendor->is_active;
    }
}
