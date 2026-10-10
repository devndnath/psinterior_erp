<?php

namespace App\Enums;

/**
 * How money moved — used by both Payments (money received from a client)
 * and Expenses (money paid out), per ARCHITECTURE.md §3.7. Shared between
 * the two modules rather than duplicated, since the set of methods a
 * local interior-design business deals with is identical either way.
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case Cheque = 'cheque';
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case Card = 'card';
    case OnlineTransfer = 'online_transfer';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Bank => 'Bank Transfer',
            self::Cheque => 'Cheque',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
            self::Card => 'Card',
            self::OnlineTransfer => 'Online Transfer',
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
