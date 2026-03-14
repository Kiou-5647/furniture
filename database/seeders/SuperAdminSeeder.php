<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'role' => UserRole::Employee,
            ]
        );

        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($superAdminRole) {
            $user->assignRole($superAdminRole);
        }
    }
}
