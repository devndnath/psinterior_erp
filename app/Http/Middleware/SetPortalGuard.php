<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes the `client` or `vendor` guard the default for everything inside
 * that portal's route group, so `auth()->user()`, `Auth::check()`,
 * `@auth`, etc. all resolve against the right guard without every
 * controller/view having to say `auth('client')` explicitly. Combined
 * with the `auth:client` / `auth:vendor` middleware on protected routes
 * inside those groups.
 */
class SetPortalGuard
{
    public function __construct(protected AuthManager $auth) {}

    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $this->auth->shouldUse($guard);

        return $next($request);
    }
}
