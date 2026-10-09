<?php

namespace App\Enums;

/**
 * A revision row is append-only — never overwritten, never deleted
 * (ARCHITECTURE.md §3.5 / spec §13). This status tracks what happened to
 * THIS specific uploaded version; the next revision always starts a fresh
 * row rather than mutating this one.
 */
enum DesignRevisionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case ClientReview = 'client_review';
    case RevisionRequired = 'revision_required';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::ClientReview => 'Client Review',
            self::RevisionRequired => 'Revision Required',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::ClientReview => 'yellow',
            self::RevisionRequired => 'purple',
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
