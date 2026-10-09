<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning = 'planning';
    case Design = 'design';
    case Quotation = 'quotation';
    case Approved = 'approved';
    case Procurement = 'procurement';
    case Execution = 'execution';
    case Inspection = 'inspection';
    case Handover = 'handover';
    case Completed = 'completed';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Design => 'Design',
            self::Quotation => 'Quotation',
            self::Approved => 'Approved',
            self::Procurement => 'Procurement',
            self::Execution => 'Execution',
            self::Inspection => 'Inspection',
            self::Handover => 'Handover',
            self::Completed => 'Completed',
            self::OnHold => 'On Hold',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Planning => 'gray',
            self::Design => 'purple',
            self::Quotation => 'indigo',
            self::Approved => 'blue',
            self::Procurement => 'yellow',
            self::Execution => 'blue',
            self::Inspection => 'yellow',
            self::Handover => 'indigo',
            self::Completed => 'green',
            self::OnHold => 'gray',
            self::Cancelled => 'red',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
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
