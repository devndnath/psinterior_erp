<?php

namespace App\Enums;

/**
 * The change order's own workflow status — distinct from
 * `client_approval_status` (App\Enums\ApprovalDecision, reused from the
 * Design module) the same way Design's `status` and
 * `client_approval_status` are two related-but-independent fields synced
 * by the service layer (ARCHITECTURE.md §3.6 / §19: approval updates
 * `projects.contract_value`). `Invoiced` is set by the Finance module
 * once an invoice is raised against this change order (a later phase).
 */
enum ChangeOrderStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Invoiced = 'invoiced';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Invoiced => 'Invoiced',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Invoiced => 'blue',
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
