<?php

namespace App\Enums;

enum SubcontractorTrade: string
{
    case Carpenter = 'carpenter';
    case Electrician = 'electrician';
    case Painter = 'painter';
    case Plumber = 'plumber';
    case Gypsum = 'gypsum';
    case Glass = 'glass';
    case Tile = 'tile';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Carpenter => 'Carpenter',
            self::Electrician => 'Electrician',
            self::Painter => 'Painter',
            self::Plumber => 'Plumber',
            self::Gypsum => 'Gypsum',
            self::Glass => 'Glass',
            self::Tile => 'Tile',
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
