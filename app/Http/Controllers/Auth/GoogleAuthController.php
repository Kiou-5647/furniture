<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleGoogleLoginAction;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class GoogleAuthController
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(HandleGoogleLoginAction $handleLogin): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = $handleLogin->execute($googleUser);

            Auth::login($user);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['auth' => 'Google authentication failed.']);
        }
    }
}
