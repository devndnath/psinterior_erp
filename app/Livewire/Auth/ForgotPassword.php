<?php

namespace App\Livewire\Auth;

class ForgotPassword extends BaseForgotPassword
{
    protected function broker(): string
    {
        return 'users';
    }

    protected function viewName(): string
    {
        return 'livewire.auth.forgot-password';
    }
}
