<?php

namespace App\Enums;

/**
 * Pulled forward from Phase 3 (Projects) because `leads.project_type`
 * needs it in Phase 2 already — a lead's "what kind of project is this"
 * answer must carry over unchanged when it becomes a project, so both
 * modules share this one enum rather than each keeping a parallel list.
 */
enum ProjectType: string
{
    case Residential = 'residential';
    case Commercial = 'commercial';
    case Office = 'office';
    case Restaurant = 'restaurant';
    case Hotel = 'hotel';
    case Showroom = 'showroom';
    case Retail = 'retail';
    case Architecture = 'architecture';
    case Renovation = 'renovation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential',
            self::Commercial => 'Commercial',
            self::Office => 'Office',
            self::Restaurant => 'Restaurant',
            self::Hotel => 'Hotel',
            self::Showroom => 'Showroom',
            self::Retail => 'Retail',
            self::Architecture => 'Architecture',
            self::Renovation => 'Renovation',
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
