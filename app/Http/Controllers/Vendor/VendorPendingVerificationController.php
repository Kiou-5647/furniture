<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class VendorPendingVerificationController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('vendor/PendingVerification');
    }
}
