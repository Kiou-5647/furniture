<?php

namespace App\Listeners\Auth;

use App\Services\Public\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MergeCartListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected CartService $cartService,
        protected Request $request
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $guestSessionId = session('guest_session_id') ?? $this->request->getSession()->getId();

        if ($guestSessionId && $user) {
            Log::info('Merging cart for user: ' . $user->id . ' with session: ' . $guestSessionId);
            $this->cartService->merge($guestSessionId, $user);
        }
    }
}
