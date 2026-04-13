<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VnPayStatusController
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('payment/vnpay-status', [
            'error' => $request->session()->get('error'),
            'response' => $request->session()->get('vnp_response'),
        ]);
    }
}
