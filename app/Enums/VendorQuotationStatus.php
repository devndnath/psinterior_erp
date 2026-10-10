<?php

namespace App\Enums;

/**
 * A vendor quotation's workflow status. `selectWinner()`
 * (VendorQuotationService) is the one action that flips a sibling
 * quotation to Rejected and this one to Selected, inside one
 * transaction — mirroring BoqService::activate()'s "flip siblings"
 * pattern from Phase 5.
 */
enum VendorQuotationStatus: string
{
    case Received = 'received';
    case Selected = 'selected';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Received',
            self::Selected => 'Selected',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Received => 'blue',
            self::Selected => 'green',
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
