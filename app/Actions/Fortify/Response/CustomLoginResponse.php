<?php

namespace App\Actions\Fortify\Response;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse;

class CustomLoginResponse implements LoginResponse
{
    /**
     * Create the HTTP response that represents the object.
     */
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();
        if ($user->isEmployee()) {
            return redirect()->intended('/dashboard');
        } elseif ($user->isVendor()) {
            return redirect()->intended('/vendor/dashboard');
        }

        return redirect()->intended('/');
    }
}
