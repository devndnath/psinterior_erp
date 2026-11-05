<?php

namespace App\Reports;

use Carbon\CarbonImmutable;

/**
 * The filter values a report receives from the viewer. Empty string /
 * null = not filtered.
 */
class Filters
{
    public function __construct(
        public ?CarbonImmutable $from = null,
        public ?CarbonImmutable $to = null,
        public string $project = '',
        public string $client = '',
        public string $vendor = '',
        public string $employee = '',
        public string $user = '',
        public string $category = '',
        public string $method = '',
    ) {}

    /** Limits $query to the date range on $column (whole days). */
    public function dates($query, string $column)
    {
        return $query
            ->when($this->from, fn ($q) => $q->whereDate($column, '>=', $this->from->toDateString()))
            ->when($this->to, fn ($q) => $q->whereDate($column, '<=', $this->to->toDateString()));
    }

    /** "1 Oct 2026 – 31 Oct 2026", or "All time". */
    public function rangeLabel(): string
    {
        if (! $this->from && ! $this->to) {
            return 'All time';
        }

        return ($this->from?->format('j M Y') ?? 'Start').' – '.($this->to?->format('j M Y') ?? 'Today');
    }

    /** Payroll months ('Y-m') covered by the range, for month-keyed tables. */
    public function monthBounds(): array
    {
        return [$this->from?->format('Y-m'), $this->to?->format('Y-m')];
    }
}
