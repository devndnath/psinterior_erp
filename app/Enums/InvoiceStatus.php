<?php

namespace App\Enums;

/**
 * The invoice's own workflow/payment status (ARCHITECTURE.md §3.7). Moves
 * Draft -> Issued via InvoiceService::issue() (gated by the distinct
 * `approve_invoice` ability, mirroring ChangeOrder's recordDecision /
 * Quotation's approve_quotation pattern), then PartiallyPaid/Paid are set
 * automatically by InvoiceService::recalculatePaymentStatus() whenever a
 * payment against it is recorded or deleted. `Overdue` and `Cancelled`
 * are the two other terminal-ish states — overdue is not yet flipped by
 * any cron job in this phase (see PHASE6_SUMMARY.md "Known issues").
 */
enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Issued',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Issued => 'blue',
            self::PartiallyPaid => 'yellow',
            self::Paid => 'green',
            self::Overdue => 'red',
            self::Cancelled => 'gray',
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
