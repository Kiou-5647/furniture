<?php

namespace App\Actions\Fortify\Response;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class CustomRegisterResponse implements RegisterResponse
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        return match ($user?->role?->value) {
            'employee' => redirect('/dashboard'),
            'vendor' => redirect('/vendor/dashboard'),
            default => redirect('/'),
        };
    }
}
