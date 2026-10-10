<?php

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_purchase_orders');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isStaff() && $user->can('view_purchase_orders');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_purchase_orders');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isStaff()
            && $user->can('edit_purchase_orders')
            && $purchaseOrder->status === PurchaseOrderStatus::Draft;
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isStaff()
            && $user->can('delete_purchase_orders')
            && $purchaseOrder->status === PurchaseOrderStatus::Draft;
    }

    /**
     * No separate approval ability exists for sending/cancelling a PO —
     * same documented simplification as VendorQuotationPolicy::selectWinner(),
     * gated on `edit_purchase_orders` rather than an unseeded ability.
     */
    public function send(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isStaff() && $user->can('edit_purchase_orders');
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isStaff() && $user->can('edit_purchase_orders');
    }
}
