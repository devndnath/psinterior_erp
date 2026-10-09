<?php

namespace App\Enums;

enum QuotationItemType: string
{
    case Item = 'item';
    case Labour = 'labour';
    case Material = 'material';
    case Furniture = 'furniture';
    case DesignFee = 'design_fee';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Item => 'Item',
            self::Labour => 'Labour',
            self::Material => 'Material',
            self::Furniture => 'Furniture',
            self::DesignFee => 'Design Fee',
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
