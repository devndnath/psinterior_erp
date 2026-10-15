<?php

namespace App\Enums;

enum ExpenseClaimType: string
{
    case Conveyance = 'conveyance';
    case Meal = 'meal';
    case Accommodation = 'accommodation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Conveyance => 'Conveyance',
            self::Meal => 'Meal',
            self::Accommodation => 'Accommodation',
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
