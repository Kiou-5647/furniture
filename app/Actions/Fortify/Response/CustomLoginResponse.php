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
            return redirect()->intended(route('employee.dashboard'));
        } elseif ($user->isVendor()) {
            return redirect()->intended(route('vendor.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
