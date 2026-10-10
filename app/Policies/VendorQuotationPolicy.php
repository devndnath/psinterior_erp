<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorQuotation;

/**
 * Same `*_purchase_orders` gating as PurchaseRequestPolicy, for the same
 * reason (see its docblock) — `vendor_quotations` is not a
 * RolePermissionSeeder CRUD module either.
 */
class VendorQuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_purchase_orders');
    }

    public function view(User $user, VendorQuotation $vendorQuotation): bool
    {
        return $user->isStaff() && $user->can('view_purchase_orders');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_purchase_orders');
    }

    /**
     * No separate approval ability exists for selecting a quotation's
     * winner — unlike ChangeOrderPolicy::recordDecision()'s distinct
     * `approve_change_order` gate, there is no seeded ability for this
     * narrower action, so it is gated on `edit_purchase_orders` as a
     * documented simplification rather than inventing a new, unseeded
     * permission string.
     */
    public function selectWinner(User $user, VendorQuotation $vendorQuotation): bool
    {
        return $user->isStaff() && $user->can('edit_purchase_orders');
    }
}
