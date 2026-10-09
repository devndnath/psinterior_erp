<?php

namespace App\Enums;

/**
 * Note: this enum seeds the default options only. The actual
 * `lead_sources` table (ARCHITECTURE.md §4) is admin-editable — Admin can
 * add a new source without a deploy. This enum exists so the seeder and
 * any place that needs "the standard starting list" has one source of
 * truth for it.
 */
enum LeadSource: string
{
    case Facebook = 'facebook';
    case Website = 'website';
    case Referral = 'referral';
    case WhatsApp = 'whatsapp';
    case Phone = 'phone';
    case WalkIn = 'walk_in';
    case Instagram = 'instagram';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Website => 'Website',
            self::Referral => 'Referral',
            self::WhatsApp => 'WhatsApp',
            self::Phone => 'Phone',
            self::WalkIn => 'Walk-in',
            self::Instagram => 'Instagram',
            self::Other => 'Other',
        };
    }
}
