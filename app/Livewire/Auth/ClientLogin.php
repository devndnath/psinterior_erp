<?php

namespace App\Livewire\Auth;

class ClientLogin extends BaseLogin
{
    protected function guardName(): string
    {
        return 'client';
    }

    protected function redirectTo(): string
    {
        return 'client.dashboard';
    }

    protected function viewName(): string
    {
        return 'livewire.auth.client-login';
    }
}
