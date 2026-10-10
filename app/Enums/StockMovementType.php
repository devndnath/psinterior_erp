<?php

namespace App\Enums;

/**
 * Classifies a `stock_movements` ledger row (ARCHITECTURE.md §3.8).
 * `ReturnStock` is named that way (not `Return`) only because `Return` is
 * a reserved word in PHP enum case names — its value stays `'return'`.
 * Not displayed as a status badge (no color()), just a ledger label.
 */
enum StockMovementType: string
{
    case Opening = 'opening';
    case Purchase = 'purchase';
    case Issue = 'issue';
    case ReturnStock = 'return';
    case Damage = 'damage';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Opening Stock',
            self::Purchase => 'Purchase',
            self::Issue => 'Issue',
            self::ReturnStock => 'Return',
            self::Damage => 'Damage',
            self::Adjustment => 'Adjustment',
            self::TransferIn => 'Transfer In',
            self::TransferOut => 'Transfer Out',
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
