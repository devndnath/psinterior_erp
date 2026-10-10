<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/**
 * No update() here on purpose — a recorded payment is never edited, only
 * deleted and re-entered if it was a mistake, mirroring real accounting
 * practice (and avoiding a half-corrected payment silently drifting from
 * the bank statement it was meant to match).
 */
class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_payments');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->isStaff() && $user->can('view_payments');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_payments');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->isStaff() && $user->can('delete_payments');
    }
}
