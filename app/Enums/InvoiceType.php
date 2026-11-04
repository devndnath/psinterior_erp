<?php

namespace App\Enums;

/**
 * What an invoice is being raised for (ARCHITECTURE.md §3.7 — Finance).
 * Purely descriptive: it does not drive any workflow transition, unlike
 * InvoiceStatus.
 */
enum InvoiceType: string
{
    case Advance = 'advance';
    case Progress = 'progress';
    case Material = 'material';
    case AdditionalWork = 'additional_work';
    case Final = 'final';
    // A design sold on its own (or billed as design work) — the only
    // type an invoice without a project uses by default.
    case Design = 'design';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Advance => 'Advance',
            self::Progress => 'Progress',
            self::Material => 'Material',
            self::AdditionalWork => 'Additional Work',
            self::Final => 'Final',
            self::Design => 'Design Sale',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Advance => 'blue',
            self::Progress => 'indigo',
            self::Material => 'yellow',
            self::AdditionalWork => 'purple',
            self::Final => 'green',
            self::Design => 'purple',
            self::Other => 'gray',
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
