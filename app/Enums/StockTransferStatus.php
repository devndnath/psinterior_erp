<?php

namespace App\Enums;

/**
 * A stock transfer moves no stock while Pending or InTransit — only
 * `StockTransferService::complete()` calls into InventoryService, since a
 * transfer in transit has physically left the source but hasn't arrived
 * yet (a deliberate simplification, documented on the service).
 */
enum StockTransferStatus: string
{
    case Pending = 'pending';
    case InTransit = 'in_transit';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InTransit => 'In Transit',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::InTransit => 'blue',
            self::Completed => 'green',
            self::Cancelled => 'red',
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
