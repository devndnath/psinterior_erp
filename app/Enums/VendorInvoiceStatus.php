<?php

namespace App\Enums;

/**
 * A vendor-submitted invoice's review status. Approving one creates the
 * corresponding payable Expense row (VendorInvoiceService::approve()) —
 * rejecting one is terminal, same as VendorQuotationStatus::Rejected.
 */
enum VendorInvoiceStatus: string
{
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'blue',
            self::Approved => 'green',
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
