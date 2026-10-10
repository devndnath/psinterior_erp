<?php

namespace App\Policies;

use App\Models\GoodsReceive;
use App\Models\User;

/**
 * No update() or delete() — a GRN is an immutable receiving record once
 * created, the same "never edited, only corrected by a new entry"
 * precedent PaymentPolicy set for Payments in Phase 6. A mistaken GRN is
 * corrected by a later adjustment, never by editing or removing history
 * that stock movements have already been posted against.
 */
class GoodsReceivePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_goods_receives');
    }

    public function view(User $user, GoodsReceive $goodsReceive): bool
    {
        return $user->isStaff() && $user->can('view_goods_receives');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_goods_receives');
    }
}
