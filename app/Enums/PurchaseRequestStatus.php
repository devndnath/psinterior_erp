<?php

namespace App\Enums;

/**
 * A purchase request's own workflow status (ARCHITECTURE.md §3.8). PR
 * permissions are deliberately not a module of their own — see
 * PurchaseRequestPolicy's docblock — so this enum, like the model, is
 * gated entirely through the `*_purchase_orders` ability set.
 */
enum PurchaseRequestStatus: string
{
    case Draft = 'draft';
    case SentForQuotation = 'sent_for_quotation';
    case VendorSelected = 'vendor_selected';
    case PoCreated = 'po_created';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::SentForQuotation => 'Sent for Quotation',
            self::VendorSelected => 'Vendor Selected',
            self::PoCreated => 'PO Created',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::SentForQuotation => 'blue',
            self::VendorSelected => 'yellow',
            self::PoCreated => 'green',
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
