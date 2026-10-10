<?php

namespace App\Enums;

/**
 * Pending -> Assigned -> InProgress -> Fixed -> Verified -> Closed
 * (ARCHITECTURE.md §3.10). A snag can also be reopened back to Pending
 * from any state except Closed — see SnagService::reopen().
 */
enum SnagStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Fixed = 'fixed';
    case Verified = 'verified';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::Fixed => 'Fixed',
            self::Verified => 'Verified',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Assigned => 'blue',
            self::InProgress => 'indigo',
            self::Fixed => 'purple',
            self::Verified => 'teal',
            self::Closed => 'green',
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
