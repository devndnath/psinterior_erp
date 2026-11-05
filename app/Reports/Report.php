<?php

namespace App\Reports;

use Illuminate\Support\Collection;

/**
 * One report in the report engine (App\Livewire\Reports\Viewer renders
 * any of them). A report declares its title, group, filters, columns and
 * rows; the viewer supplies filter controls, the totals row, summary
 * cards, CSV export and print.
 *
 * Column types: text | money | int | date | pct. A text cell may be a
 * plain string or ['text' => …, 'href' => …, 'sub' => …] for a linked
 * value with a second line. Columns with 'total' => true are summed.
 */
abstract class Report
{
    /** URL key, e.g. 'project-wise'. */
    abstract public static function key(): string;

    abstract public static function title(): string;

    /** 'accounts' | 'crm' | 'all' — where it sits in the sidebar / hub. */
    abstract public static function group(): string;

    abstract public static function description(): string;

    /** @return array<string, array{label:string,type:string,total?:bool}> */
    abstract public function columns(): array;

    /** @return iterable<array<string, mixed>> */
    abstract public function rows(Filters $f): iterable;

    /** Ability needed to open it (on top of view_reports). */
    public static function permission(): string
    {
        return 'view_reports';
    }

    /** Filter controls shown: dates, project, client, vendor, employee, user, category, method. */
    public function filters(): array
    {
        return ['dates'];
    }

    /** Date range the report opens with: 'month' | 'year' | 'all'. */
    public function defaultRange(): string
    {
        return 'month';
    }

    /** One line explaining how the figures are worked out. */
    public function note(): ?string
    {
        return null;
    }

    /**
     * Summary cards above the table — by default the first few totalled
     * columns. @return array<int, array{label:string,value:float|int,type:string,tone?:string}>
     */
    public function summary(Collection $rows, array $totals): array
    {
        return collect($this->columns())
            ->filter(fn ($c) => ! empty($c['total']))
            ->take(4)
            ->map(fn ($c, $k) => ['label' => $c['label'], 'value' => $totals[$k] ?? 0, 'type' => $c['type']])
            ->values()->all();
    }

    /** Sums every column marked 'total'. */
    public function totals(Collection $rows): array
    {
        return collect($this->columns())
            ->filter(fn ($c) => ! empty($c['total']))
            ->map(fn ($c, $k) => $rows->sum(fn ($r) => (float) (is_array($r[$k] ?? null) ? 0 : ($r[$k] ?? 0))))
            ->all();
    }

    /** A text cell with an optional link and second line. */
    protected static function cell(?string $text, ?string $href = null, ?string $sub = null): array
    {
        return ['text' => $text ?? '—', 'href' => $href, 'sub' => $sub];
    }
}
