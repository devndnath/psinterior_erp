<?php

namespace App\Enums;

enum ApprovalDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RevisionRequested = 'revision_requested';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::RevisionRequested => 'Revision Requested',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Approved => 'green',
            self::Rejected => 'red',
            self::RevisionRequested => 'purple',
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
