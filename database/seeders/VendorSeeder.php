<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Auth\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        // Verified vendor (for testing vendor dashboard)
        $verifiedVendor = $this->createVendor(
            code: 'VD-VERIFY01',
            name: 'Nhà cung cấp Verified',
            contactName: 'Vendor Admin Verified',
            email: 'vendor_verified@test.com',
            phone: '0123456789',
            isVerified: true
        );
        // Unverified vendor (for testing verification flow)
        $unverifiedVendor = $this->createVendor(
            code: 'VD-UNVERIFY',
            name: 'Nhà cung cấp Chờ xác nhận',
            contactName: 'Vendor Admin Unverified',
            email: 'vendor_unverified@test.com',
            phone: '0123456788',
            isVerified: false
        );
        $this->command->info('VendorSeeder: Created 2 vendors (1 verified, 1 unverified)');
    }

    private function createVendor(
        string $code,
        string $name,
        string $contactName,
        string $email,
        string $phone,
        bool $isVerified
    ): Vendor {
        $vendor = Vendor::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'contact_name' => $contactName,
                'email' => $email,
                'phone' => $phone,
                'is_active' => true,
                'verified_at' => $isVerified ? now() : null,
            ]
        );
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $contactName,
                'password' => Hash::make('password'),
                'type' => UserType::Vendor,
                'is_active' => true,
                'email_verified_at' => $isVerified ? now() : null,
            ]
        );
        if (! $user->vendor()->exists()) {
            if (! $user->vendorUser) {
                VendorUser::create([
                    'user_id' => $user->id,
                    'vendor_id' => $vendor->id,
                    'full_name' => $contactName,
                    'phone' => $phone,
                ]);
            }
        }

        return $vendor;
    }
}
