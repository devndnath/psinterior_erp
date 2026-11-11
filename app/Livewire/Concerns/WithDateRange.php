<?php

namespace App\Livewire\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * From / To date filter for a list, kept in the URL (?from=…&to=…) so
 * other pages can link straight to "these records in this window" — the
 * CRM overview's tiles and chart bars do. Use with <x-date-range-inputs />.
 */
trait WithDateRange
{
    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function clearDateRange(): void
    {
        $this->reset(['from', 'to']);
        $this->resetPage();
    }

    /** Limit $query to the chosen window on $column; bad dates are ignored. */
    protected function applyDateRange(Builder $query, string $column): Builder
    {
        $from = $this->parsedDate($this->from);
        $to = $this->parsedDate($this->to);

        return $query
            ->when($from, fn ($q) => $q->whereDate($column, '>=', $from))
            ->when($to, fn ($q) => $q->whereDate($column, '<=', $to));
    }

    private function parsedDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
