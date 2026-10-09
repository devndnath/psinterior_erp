<?php

namespace App\Enums;

enum FollowupType: string
{
    case Call = 'call';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Meeting = 'meeting';
    case SiteVisit = 'site_visit';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::WhatsApp => 'WhatsApp',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::SiteVisit => 'Site Visit',
            self::Other => 'Other',
        };
    }

    /**
     * value => label map, the shape every dropdown in the views expects
     * (`@foreach ($options as $value => $label)`).
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
