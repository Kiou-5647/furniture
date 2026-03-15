<?php

namespace App\Actions\Fortify\Response;

use App\Services\VendorVerificationService;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse;

class CustomLoginResponse implements LoginResponse
{
    public function __construct(
        public VendorVerificationService $vendorService
    ) {}

    /**
     * Create the HTTP response that represents the object.
     */
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isEmployee()) {
            return redirect()->intended(route('employee.dashboard'));
        } elseif ($user->isVendor()) {
            if (! $this->vendorService->isVendorVerified($user)) {
                return redirect()->route('vendor.pending-verification');
            }

            return redirect()->intended(route('vendor.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
