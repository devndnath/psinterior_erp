<?php

namespace App\Policies;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;

/**
 * Purchase Requests and Vendor Quotations are sub-steps of the same
 * procurement pipeline that the `purchase_orders` module's permissions
 * already gate — RolePermissionSeeder's `$crudModules` list includes
 * `purchase_orders` but deliberately NOT `purchase_requests` or
 * `vendor_quotations`, so `view_purchase_requests`/`edit_vendor_quotations`
 * etc. are never created by the seeder and would be false for every
 * non-super-admin role. Rather than editing the seeder (not needed —
 * everything required already exists), every ability here is gated on
 * the existing `*_purchase_orders` permissions instead, which
 * `procurement-officer`'s role definition already holds
 * (view/create/edit_purchase_orders) and which is clearly meant to run
 * this whole pipeline end to end.
 */
class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_purchase_orders');
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->isStaff() && $user->can('view_purchase_orders');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_purchase_orders');
    }

    /**
     * Restricted to Draft, mirroring MaterialRequisitionPolicy::update()'s
     * "Pending-only" restriction — once sent for quotation, a request's
     * items are what vendors are quoting against and are no longer
     * free-form editable.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->isStaff()
            && $user->can('edit_purchase_orders')
            && $purchaseRequest->status === PurchaseRequestStatus::Draft;
    }

    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->isStaff()
            && $user->can('delete_purchase_orders')
            && $purchaseRequest->status === PurchaseRequestStatus::Draft;
    }

    public function sendForQuotation(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->isStaff() && $user->can('edit_purchase_orders');
    }

    public function cancel(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->isStaff() && $user->can('edit_purchase_orders');
    }
}
