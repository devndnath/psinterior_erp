<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Shared login logic for the three portals. Concrete subclasses
 * (Login/ClientLogin/VendorLogin) just name their guard and where a
 * successful login should land — everything else (validation, throttling,
 * remember-me, audit) lives here once.
 */
abstract class BaseLogin extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    abstract protected function guardName(): string;

    abstract protected function redirectTo(): string;

    abstract protected function viewName(): string;

    public function submit(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::guard($this->guardName())->attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        $this->redirectRoute($this->redirectTo(), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate($this->guardName().'|'.Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view($this->viewName())->layout('layouts.guest');
    }
}
