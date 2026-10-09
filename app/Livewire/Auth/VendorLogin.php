<?php

namespace App\Livewire\Auth;

class VendorLogin extends BaseLogin
{
    protected function guardName(): string
    {
        return 'vendor';
    }

    protected function redirectTo(): string
    {
        return 'vendor.dashboard';
    }

    protected function viewName(): string
    {
        return 'livewire.auth.vendor-login';
    }
}
