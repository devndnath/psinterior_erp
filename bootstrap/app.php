<?php

use App\Http\Middleware\EnsureUserType;
use App\Http\Middleware\SanitizeUtf8Input;
use App\Http\Middleware\SetPortalGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Staff portal (default `web` guard) lives in routes/web.php.
            // Client and vendor portals are separate route files with their
            // own prefix, name-space and guard so a client session can never
            // resolve staff routes and vice versa (ARCHITECTURE.md §5/§7).
            Illuminate\Support\Facades\Route::middleware(['web', SetPortalGuard::class.':client'])
                ->prefix('client')
                ->name('client.')
                ->group(base_path('routes/client.php'));

            Illuminate\Support\Facades\Route::middleware(['web', SetPortalGuard::class.':vendor'])
                ->prefix('vendor')
                ->name('vendor.')
                ->group(base_path('routes/vendor.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'user_type' => EnsureUserType::class,
        ]);

        // Runs before anything else touches request input — including
        // Livewire's own property hydration — so a stray invalid UTF-8
        // byte sequence never reaches a component property in the first
        // place (see SanitizeUtf8Input's docblock for the crash this
        // prevents).
        $middleware->web(prepend: [
            SanitizeUtf8Input::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LogsLastActivity::class,
        ]);

        // Three portals, three "where do I send you" destinations. Without
        // this, Laravel's default 'auth'/'guest' middleware would always
        // redirect to the staff `login`/`dashboard` routes regardless of
        // which portal the guest/authenticated request came from.
        $middleware->redirectGuestsTo(fn ($request) => match (true) {
            $request->is('client/*') => route('client.login'),
            $request->is('vendor/*') => route('vendor.login'),
            default => route('login'),
        });

        $middleware->redirectUsersTo(fn ($request) => match (true) {
            $request->is('client/*') => route('client.dashboard'),
            $request->is('vendor/*') => route('vendor.dashboard'),
            default => route('dashboard'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Technical details go to the log; the person only ever sees a
        // clean, generic message for 500s (ARCHITECTURE.md / spec §63).
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if (! app()->environment(['local', 'testing']) && $response->getStatusCode() === 500) {
                return response()->view('errors.500', [], 500);
            }

            return $response;
        });
    })->create();
