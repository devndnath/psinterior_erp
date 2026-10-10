<?php

namespace App\Enums;

/**
 * Approval chain for a Material Requisition: Pending -> ManagerApproved
 * -> StoreApproved -> Issued, with Rejected reachable from Pending or
 * ManagerApproved (ARCHITECTURE.md §3.8 / spec §81). Issuing decrements
 * warehouse stock via InventoryService — the only step in this chain
 * that mutates `warehouse_stocks`.
 */
enum MaterialRequisitionStatus: string
{
    case Pending = 'pending';
    case ManagerApproved = 'manager_approved';
    case StoreApproved = 'store_approved';
    case Issued = 'issued';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::ManagerApproved => 'Manager Approved',
            self::StoreApproved => 'Store Approved',
            self::Issued => 'Issued',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::ManagerApproved => 'blue',
            self::StoreApproved => 'indigo',
            self::Issued => 'green',
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
