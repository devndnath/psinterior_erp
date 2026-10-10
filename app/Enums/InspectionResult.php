<?php

namespace App\Enums;

/**
 * Used both as an Inspection's own overall result and as each
 * InspectionItem's per-line result (ARCHITECTURE.md §3.10). Ordered
 * worst-to-best is Failed > RequiresCorrection > Passed — see
 * InspectionService::computeOverallResult() for where that ordering
 * is actually used.
 */
enum InspectionResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case RequiresCorrection = 'requires_correction';

    public function label(): string
    {
        return match ($this) {
            self::Passed => 'Passed',
            self::Failed => 'Failed',
            self::RequiresCorrection => 'Requires Correction',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Passed => 'green',
            self::Failed => 'red',
            self::RequiresCorrection => 'yellow',
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
