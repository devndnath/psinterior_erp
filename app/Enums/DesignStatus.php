<?php

namespace App\Enums;

/**
 * The design's own staff-facing workflow stage — kept in sync by
 * DesignService as revisions are added and client decisions recorded,
 * never edited directly (ARCHITECTURE.md §3.5). A design's current
 * client-facing answer lives separately on `client_approval_status`
 * (ApprovalDecision), since the two can disagree — e.g. a design can be
 * `rejected` here while still sitting on an un-superseded `approved`
 * decision from an earlier revision.
 */
enum DesignStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::UnderReview => 'yellow',
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
