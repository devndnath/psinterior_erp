<?php

namespace App\Enums;

/**
 * Discriminates the three audiences that share the single `users` table
 * (ARCHITECTURE.md §1 assumption 9, §5). Staff authenticate on the `web`
 * guard, Client portal users on `client`, Vendor portal users on `vendor` —
 * App\Providers\AuthServiceProvider scopes each guard's provider to the
 * matching value here so a login can never cross into another guard.
 */
enum UserType: string
{
    case Staff = 'staff';
    case Client = 'client';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Client => 'Client',
            self::Vendor => 'Vendor',
        };
    }

    public function guard(): string
    {
        return match ($this) {
            self::Staff => 'web',
            self::Client => 'client',
            self::Vendor => 'vendor',
        };
    }
}
