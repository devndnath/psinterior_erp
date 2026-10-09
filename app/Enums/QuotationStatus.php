<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Negotiation = 'negotiation';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Viewed => 'Viewed',
            self::Negotiation => 'Negotiation',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Viewed => 'indigo',
            self::Negotiation => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Expired => 'gray',
        };
    }

    /**
     * Once a quotation has left Draft, editing it directly is no longer
     * safe (ARCHITECTURE.md §3.6 / spec §78 rule 1) — QuotationService
     * snapshots the current state into quotation_versions first.
     */
    public function isEditableDirectly(): bool
    {
        return $this === self::Draft;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected, self::Expired], true);
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
