<?php

namespace App\Enums;

enum LeaveType: string
{
    case Casual = 'casual';
    case Sick = 'sick';
    case Annual = 'annual';
    case Emergency = 'emergency';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Casual => 'Casual',
            self::Sick => 'Sick',
            self::Annual => 'Annual',
            self::Emergency => 'Emergency',
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
