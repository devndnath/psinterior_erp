<?php

namespace App\Enums;

/**
 * Open -> TechnicianAssigned -> SiteVisit -> Repair -> Verification ->
 * Closed (ARCHITECTURE.md §3.10, post-handover warranty support). See
 * ServiceRequestPolicy for why this whole module reuses the `*_warranties`
 * permissions rather than an unseeded `service_requests` module.
 */
enum ServiceRequestStatus: string
{
    case Open = 'open';
    case TechnicianAssigned = 'technician_assigned';
    case SiteVisit = 'site_visit';
    case Repair = 'repair';
    case Verification = 'verification';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::TechnicianAssigned => 'Technician Assigned',
            self::SiteVisit => 'Site Visit',
            self::Repair => 'Repair',
            self::Verification => 'Verification',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'yellow',
            self::TechnicianAssigned => 'blue',
            self::SiteVisit => 'indigo',
            self::Repair => 'purple',
            self::Verification => 'teal',
            self::Closed => 'green',
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
