<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defence-in-depth behind the guard scoping in
 * App\Providers\AuthServiceProvider: even if a `users` row's `user_type`
 * were somehow mismatched with the guard it logged in on, this blocks the
 * request rather than trusting the session alone. Register per-route as
 * `user_type:staff`, `user_type:client`, or `user_type:vendor`.
 */
class EnsureUserType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = $request->user();

        if (! $user || $user->user_type !== UserType::from($type)) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
