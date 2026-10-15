<?php

namespace App\Policies;

use App\Models\ExpenseClaim;
use App\Models\User;

/**
 * Gates the ADMIN-side view/review of expense claims
 * (`view_expense_claims`/`edit_expense_claims`, seeded to hr-manager and
 * accountant — see RolePermissionSeeder). Deliberately NOT used by the
 * self-service My Expense Claims screens (App\Livewire\MyExpenseClaims):
 * an employee submitting or viewing their OWN claim is authorized there
 * directly against "is this their linked Employee record", the same
 * split already established for Leave self-service vs LeavePolicy.
 * `approve()`/`reject()` share the single `edit_expense_claims` ability
 * — there's no manager-vs-HR two-stage split here like Leave has, so one
 * reviewer ability is enough (mirrors ExpensePolicy's own simplicity).
 */
class ExpenseClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_expense_claims');
    }

    public function view(User $user, ExpenseClaim $claim): bool
    {
        return $user->isStaff() && $user->can('view_expense_claims');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_expense_claims');
    }

    public function update(User $user, ExpenseClaim $claim): bool
    {
        return $user->isStaff() && $user->can('edit_expense_claims') && $claim->isPending();
    }

    public function delete(User $user, ExpenseClaim $claim): bool
    {
        return $user->isStaff() && $user->can('delete_expense_claims') && $claim->isPending();
    }

    public function approve(User $user, ExpenseClaim $claim): bool
    {
        return $user->isStaff() && $user->can('edit_expense_claims') && $claim->isPending();
    }

    public function reject(User $user, ExpenseClaim $claim): bool
    {
        return $user->isStaff() && $user->can('edit_expense_claims') && $claim->isPending();
    }
}
