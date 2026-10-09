<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, string $guard = 'web'): RedirectResponse
    {
        auth($guard)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return match ($guard) {
            'client' => redirect()->route('client.login'),
            'vendor' => redirect()->route('vendor.login'),
            default => redirect()->route('login'),
        };
    }
}
