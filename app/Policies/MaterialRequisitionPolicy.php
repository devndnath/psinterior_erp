<?php

namespace App\Policies;

use App\Enums\MaterialRequisitionStatus;
use App\Models\MaterialRequisition;
use App\Models\User;

class MaterialRequisitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_material_requisitions');
    }

    public function view(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff() && $user->can('view_material_requisitions');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_material_requisitions');
    }

    /**
     * Restricted to Pending the same way Invoice's update() is restricted
     * to "not yet Paid" — a deliberate design call beyond the bare spec:
     * once a requisition has entered the approval chain, its items are
     * part of what managers/store are approving and are no longer
     * free-form editable.
     */
    public function update(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff()
            && $user->can('edit_material_requisitions')
            && $materialRequisition->status === MaterialRequisitionStatus::Pending;
    }

    /**
     * Mirrors InvoicePolicy's "Paid invoice is never deleted" rule: once
     * a requisition has moved beyond Pending, it's already part of an
     * approval/issuing trail and is never deleted.
     */
    public function delete(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff()
            && $user->can('delete_material_requisitions')
            && $materialRequisition->status === MaterialRequisitionStatus::Pending;
    }

    public function approveByManager(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff() && $user->can('edit_material_requisitions');
    }

    /**
     * Store-level actions — gated on the distinct `manage_inventory`
     * ability rather than general edit, the same deliberately narrower
     * gate used elsewhere for actions that mutate stock or finalize a
     * document (ChangeOrderPolicy::recordDecision(),
     * InvoicePolicy::issue()).
     */
    public function approveByStore(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff() && $user->can('manage_inventory');
    }

    public function issue(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff() && $user->can('manage_inventory');
    }

    public function reject(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->isStaff() && $user->can('edit_material_requisitions');
    }
}
