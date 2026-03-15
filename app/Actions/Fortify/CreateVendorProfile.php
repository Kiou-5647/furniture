<?php

namespace App\Actions\Fortify;

use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorUser;
use Illuminate\Support\Facades\DB;

class CreateVendorProfile
{
    /**
     * Create a vendor profile for the given user.
     */
    public function create($user, array $input = []): Vendor
    {
        return DB::transaction(function () use ($user, $input) {
            $vendor = Vendor::create([
                'name' => $input['vendor_name'],
                'code' => $this->generateUniqueVendorCode(),
                'contact_name' => $input['name'] ?? null,
                'email' => $input['email'] ?? null,
                'phone' => $input['phone'] ?? null,
                'is_active' => true,
                'is_verified' => false,
                'payment_terms_days' => 30,
                'lead_time_days' => 7,
                'minimum_order_amount' => 0,
                'rating' => 0,
                'total_orders' => 0,
                'total_revenue' => 0.00,
            ]);
            VendorUser::create([
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'full_name' => $input['name'] ?? null,
                'phone' => $input['phone'] ?? null,
            ]);

            return $vendor;
        });
    }

    /**
     * Generate unique vendor code.
     */
    private function generateUniqueVendorCode(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $random = '';
            for ($i = 0; $i < 8; $i++) {
                $random .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $code = 'VD='.$random;
        } while (Vendor::where('code', $code)->exists());

        return $code;
    }
}
