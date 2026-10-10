<?php

namespace App\Enums;

/**
 * A short, fixed, display/filtering-only classification for a warehouse
 * (main store, a site store, a furniture/hardware store, or other) —
 * kept as an enum for consistency with the rest of this codebase's
 * enum-heavy style, the same treatment given to every other short
 * descriptive status field.
 */
enum WarehouseType: string
{
    case Main = 'main';
    case Site = 'site';
    case Furniture = 'furniture';
    case Hardware = 'hardware';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Main => 'Main',
            self::Site => 'Site',
            self::Furniture => 'Furniture',
            self::Hardware => 'Hardware',
            self::Other => 'Other',
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
