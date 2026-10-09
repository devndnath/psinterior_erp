<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

abstract class BaseForgotPassword extends Component
{
    public string $email = '';

    public string $status = '';

    abstract protected function broker(): string;

    abstract protected function viewName(): string;

    public function sendResetLink(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        $status = Password::broker($this->broker())->sendResetLink(
            ['email' => $this->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->reset('email');

            return;
        }

        $this->addError('email', __($status));
    }

    public function render()
    {
        return view($this->viewName())->layout('layouts.guest');
    }
}
