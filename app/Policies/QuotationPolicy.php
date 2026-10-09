<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_quotations');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->isStaff() && $user->can('view_quotations');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_quotations');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->isStaff() && $user->can('edit_quotations');
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->isStaff() && $user->can('delete_quotations');
    }

    /**
     * Sending/viewing/negotiation transitions reuse `update` — only the
     * final client-decision transition (approve/reject) is gated by the
     * distinct `approve_quotation` ability already seeded in Phase 1,
     * matching Design's uploadRevision/recordApproval split.
     */
    public function recordDecision(User $user, Quotation $quotation): bool
    {
        return $user->isStaff() && $user->can('approve_quotation');
    }
}
