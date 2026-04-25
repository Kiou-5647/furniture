<?php

namespace App\Actions\Auth;

use App\Models\Auth\User;
use App\Actions\Fortify\CreateCustomerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleGoogleLoginAction
{
    public function execute(SocialiteUser $googleUser): User
    {
        return DB::transaction(function () use ($googleUser) {
            // 1. Find by Google ID
            $user = User::where('google_id', $googleUser->getId())->first();

            if ($user) {
                return $user;
            }

            // 2. Find by Email to link existing account
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
                return $user;
            }

            // 3. Create new User following your Fortify pattern
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(24)),
                'type' => 'customer',
                'google_id' => $googleUser->getId(),
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            // 4. Reuse your existing profile creator
            (new CreateCustomerProfile)->create($user, [
                'name' => $googleUser->getName(),
            ]);

            return $user;
        });
    }
}
