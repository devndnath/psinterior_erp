<?php

namespace App\Enums;

/**
 * A selection board's lifecycle: staff assemble options (Draft), share
 * it with the client (Sent), the client picks one (Decided) — or it's
 * withdrawn before a decision (Cancelled). Unlike Design/DesignStatus
 * there's no separate client-facing field: a board has exactly one
 * decision, ever, so this single status is the whole story.
 */
enum SelectionBoardStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Decided = 'decided';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent to Client',
            self::Decided => 'Decided',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Decided => 'green',
            self::Cancelled => 'red',
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
