<?php

namespace App\Policies;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use App\Models\User;

/**
 * `finalize()`/`markPaid()` are gated on the distinct `manage_payroll`
 * ability (seeded only to `hr-manager`), not on general `edit_payroll` —
 * mirroring how ChangeOrderPolicy::recordDecision() and
 * MaterialRequisitionPolicy::approveByStore() use a narrower, already-
 * seeded ability for the action that actually closes out a document,
 * rather than inventing a new one. `update()`/`delete()` are restricted
 * to Draft, mirroring MaterialRequisitionPolicy's Pending-only
 * restriction and InvoiceService's "never touch it once it's left an
 * editable state" pattern.
 */
class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_payroll');
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->isStaff() && $user->can('view_payroll');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_payroll');
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->isStaff()
            && $user->can('edit_payroll')
            && $payroll->status === PayrollStatus::Draft;
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->isStaff()
            && $user->can('delete_payroll')
            && $payroll->status === PayrollStatus::Draft;
    }

    public function finalize(User $user, Payroll $payroll): bool
    {
        return $user->isStaff() && $user->can('manage_payroll');
    }

    public function markPaid(User $user, Payroll $payroll): bool
    {
        return $user->isStaff() && $user->can('manage_payroll');
    }
}
