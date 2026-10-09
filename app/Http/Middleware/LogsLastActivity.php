<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Touches `users.last_login_at` on each authenticated request, throttled
 * to once a minute per user so it doesn't add a write to every request.
 * Powers the "last seen" column on user listings in later phases.
 */
class LogsLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->last_login_at || $user->last_login_at->lt(now()->subMinute()))) {
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}
