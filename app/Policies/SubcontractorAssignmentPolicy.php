<?php

namespace App\Policies;

use App\Models\SubcontractorAssignment;
use App\Models\User;

/**
 * An assignment is just a subcontractor's work on a project, so it
 * reuses the parent Subcontractor's `*_subcontractors` permission set
 * rather than inventing a separate `*_subcontractor_assignments` module
 * — the same reasoning PurchaseRequestPolicy/VendorQuotationPolicy used
 * for the PR/VQ pipeline in Phase 8.
 */
class SubcontractorAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_subcontractors');
    }

    public function view(User $user, SubcontractorAssignment $assignment): bool
    {
        return $user->isStaff() && $user->can('view_subcontractors');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_subcontractors');
    }

    public function update(User $user, SubcontractorAssignment $assignment): bool
    {
        return $user->isStaff() && $user->can('edit_subcontractors');
    }

    public function delete(User $user, SubcontractorAssignment $assignment): bool
    {
        return $user->isStaff() && $user->can('delete_subcontractors');
    }
}
