<?php

namespace App\Services;

use App\Enums\ExpenseClaimStatus;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\User;

/**
 * Owns the ExpenseClaim aggregate — an employee's reimbursement request
 * (conveyance/meal/accommodation/other), reviewed by HR/Accounts and,
 * once Approved, picked up by PayrollService::computeDraft() for the
 * employee's payroll. Single-stage approval (unlike Leave's two-stage
 * manager+HR flow) — there's no manager-vs-HR split specified for
 * expense reimbursement, so one `edit_expense_claims`-gated reviewer
 * decision is enough, mirroring ExpensePolicy/ExpenseService's own
 * simplicity.
 */
class ExpenseClaimService
{
    public function create(Employee $employee, array $data): ExpenseClaim
    {
        return $employee->expenseClaims()->create([
            'project_id' => $data['project_id'] ?? null,
            'site_visit_id' => $data['site_visit_id'] ?? null,
            'type' => $data['type'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'status' => ExpenseClaimStatus::Pending->value,
        ]);
    }

    public function update(ExpenseClaim $claim, array $data): ExpenseClaim
    {
        $claim->update([
            'project_id' => $data['project_id'] ?? null,
            'site_visit_id' => $data['site_visit_id'] ?? null,
            'type' => $data['type'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return $claim->fresh();
    }

    public function approve(ExpenseClaim $claim, User $reviewer): ExpenseClaim
    {
        $claim->update([
            'status' => ExpenseClaimStatus::Approved->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $claim->fresh();
    }

    public function reject(ExpenseClaim $claim, User $reviewer): ExpenseClaim
    {
        $claim->update([
            'status' => ExpenseClaimStatus::Rejected->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $claim->fresh();
    }
}
