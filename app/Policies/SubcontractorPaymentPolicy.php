<?php

namespace App\Policies;

use App\Models\SubcontractorPayment;
use App\Models\User;

/**
 * No update()/delete() — a recorded subcontractor payment is immutable
 * once entered, mirroring PaymentPolicy's "never edited, only corrected
 * by a new entry" precedent from Phase 6. Gated on `*_subcontractors`
 * like SubcontractorAssignmentPolicy, not a separate module.
 */
class SubcontractorPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_subcontractors');
    }

    public function view(User $user, SubcontractorPayment $payment): bool
    {
        return $user->isStaff() && $user->can('view_subcontractors');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_subcontractors');
    }
}
