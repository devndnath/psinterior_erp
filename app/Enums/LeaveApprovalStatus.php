<?php

namespace App\Enums;

/**
 * Backs BOTH `leaves.manager_status` and `leaves.hr_status`
 * (ARCHITECTURE.md §3.9's two-stage approval). Deliberately kept as its
 * own enum rather than reusing `App\Enums\ApprovalDecision` — that one
 * is the client-facing design/change-order/quotation decision enum and
 * carries a different case set (including `RevisionRequested`, which has
 * no meaning for an internal HR approval). Keeping conceptually
 * different workflows on separate enums mirrors how `ChangeOrderStatus`
 * and `ApprovalDecision` were kept apart in Phase 5 even though both are
 * "pending/approved/rejected"-shaped.
 */
enum LeaveApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
