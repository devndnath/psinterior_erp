<?php

namespace App\Livewire\Auth;

class Login extends BaseLogin
{
    protected function guardName(): string
    {
        return 'web';
    }

    protected function redirectTo(): string
    {
        return 'dashboard';
    }

    protected function viewName(): string
    {
        return 'livewire.auth.login';
    }
}
