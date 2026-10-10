<?php

namespace App\Enums;

/**
 * ARCHITECTURE.md §3.9: "architected so a biometric API can insert rows
 * later" — this enum's case set is intentionally plain (no half-day,
 * no WFH) so a future biometric integration has an unambiguous status
 * to write per punch without this phase guessing at finer-grained rules
 * it was never asked to build.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Leave = 'leave';
    case Overtime = 'overtime';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::Leave => 'Leave',
            self::Overtime => 'Overtime',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Present => 'green',
            self::Absent => 'red',
            self::Late => 'yellow',
            self::Leave => 'purple',
            self::Overtime => 'blue',
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
