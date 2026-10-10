<?php

namespace App\Enums;

/**
 * Who/what kind of meeting this is (ARCHITECTURE.md §3.10). Purely
 * descriptive — no workflow is attached to the type.
 */
enum MeetingType: string
{
    case Client = 'client';
    case Site = 'site';
    case Internal = 'internal';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client Meeting',
            self::Site => 'Site Meeting',
            self::Internal => 'Internal Meeting',
            self::Vendor => 'Vendor Meeting',
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
