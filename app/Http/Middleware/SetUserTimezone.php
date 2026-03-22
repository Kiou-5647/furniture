<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $request->cookie('user_timezone', config('app.timezone', 'UTC'));

        // Just store the timezone in the request attributes for safe display
        $request->attributes->set('user_timezone', $timezone);

        return $next($request);
    }
}
