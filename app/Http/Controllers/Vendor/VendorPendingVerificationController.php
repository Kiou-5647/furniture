<?php

namespace App\Http\Controllers\Vendor;

use Inertia\Inertia;
use Inertia\Response;

class VendorPendingVerificationController
{
    public function show(): Response
    {
        return Inertia::render('vendor/PendingVerification');
    }
}
