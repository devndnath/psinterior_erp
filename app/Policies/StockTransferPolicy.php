<?php

namespace App\Policies;

use App\Models\StockTransfer;
use App\Models\User;

/**
 * A transfer is a warehouse operation, so its basic CRUD-style abilities
 * ride on the `*_warehouses` permissions. Shipping/completing/cancelling
 * it — the actions that actually move or lock in stock — are gated on
 * the distinct `manage_inventory` ability instead, mirroring
 * ChangeOrderPolicy::recordDecision()'s "a more privileged action than
 * just creating the record" pattern. There is no delete: once created, a
 * transfer is part of the audit trail and is only ever cancelled, never
 * removed.
 */
class StockTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_warehouses');
    }

    public function view(User $user, StockTransfer $stockTransfer): bool
    {
        return $user->isStaff() && $user->can('view_warehouses');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_warehouses');
    }

    public function ship(User $user, StockTransfer $stockTransfer): bool
    {
        return $user->isStaff() && $user->can('manage_inventory');
    }

    public function complete(User $user, StockTransfer $stockTransfer): bool
    {
        return $user->isStaff() && $user->can('manage_inventory');
    }

    public function cancel(User $user, StockTransfer $stockTransfer): bool
    {
        return $user->isStaff() && $user->can('manage_inventory');
    }
}
