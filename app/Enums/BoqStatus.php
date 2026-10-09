<?php

namespace App\Enums;

/**
 * A BOQ's own lifecycle, independent of any Quotation built from it.
 * Only one BOQ per project is ever `Active` — creating a new version
 * (BoqService::createVersion) flips the previous active one to
 * `Superseded` rather than deleting or overwriting it, so the project's
 * BOQ history stays intact (ARCHITECTURE.md §3.6).
 */
enum BoqStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Superseded => 'Superseded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::Superseded => 'gray',
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
