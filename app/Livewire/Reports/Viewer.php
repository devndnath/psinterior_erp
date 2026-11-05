<?php

namespace App\Livewire\Reports;

use App\Enums\PaymentMethod;
use App\Models\Client;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use App\Reports\Filters;
use App\Reports\Registry;
use App\Reports\Report;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Renders any report from App\Reports\Registry: the filters the report
 * asks for, summary cards, the table with a totals row, CSV export and
 * print. One page for every report, so they all look and behave alike.
 */
#[Layout('layouts.admin')]
class Viewer extends Component
{
    public string $report = '';

    #[Url] public string $from = '';
    #[Url] public string $to = '';
    #[Url] public string $project = '';
    #[Url] public string $client = '';
    #[Url] public string $vendor = '';
    #[Url] public string $employee = '';
    #[Url] public string $user = '';
    #[Url] public string $category = '';
    #[Url] public string $method = '';

    public function mount(string $report): void
    {
        $class = Registry::find($report);
        abort_unless($class, 404);
        abort_unless(auth()->user()?->can('view_reports') && auth()->user()->can($class::permission()), 403);

        $this->report = $report;

        // Opening range, unless the URL already carries one.
        if ($this->from === '' && $this->to === '' && ! request()->has('from') && ! request()->has('to')) {
            $this->applyRange($this->definition()->defaultRange());
        }
    }

    protected function definition(): Report
    {
        $class = Registry::find($this->report);

        return new $class;
    }

    public function applyRange(string $range): void
    {
        $today = CarbonImmutable::today();
        [$from, $to] = match ($range) {
            'today' => [$today, $today],
            'week' => [$today->startOfWeek(), $today->endOfWeek()],
            'month' => [$today->startOfMonth(), $today->endOfMonth()],
            'last-month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'year' => [$today->startOfYear(), $today->endOfYear()],
            default => [null, null],
        };
        $this->from = $from?->toDateString() ?? '';
        $this->to = $to?->toDateString() ?? '';
    }

    public function resetFilters(): void
    {
        $this->reset(['project', 'client', 'vendor', 'employee', 'user', 'category', 'method']);
        $this->applyRange($this->definition()->defaultRange());
    }

    protected function filters(): Filters
    {
        $date = fn ($v) => $v !== '' && strtotime($v) ? CarbonImmutable::parse($v)->startOfDay() : null;

        return new Filters($date($this->from), $date($this->to), $this->project, $this->client,
            $this->vendor, $this->employee, $this->user, $this->category, $this->method);
    }

    /** @return array{0: Report, 1: Collection, 2: array, 3: Filters} */
    protected function compute(): array
    {
        $def = $this->definition();
        $f = $this->filters();
        $rows = collect($def->rows($f))->values();

        return [$def, $rows, $def->totals($rows), $f];
    }

    /** Plain text for a cell (CSV and titles). */
    public static function plain(mixed $value, string $type): string
    {
        if (is_array($value)) {
            return (string) ($value['text'] ?? '');
        }
        if ($value === null || $value === '') {
            return '';
        }

        return match ($type) {
            'money' => number_format((float) $value, 2, '.', ''),
            'qty' => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.'),
            'date' => CarbonImmutable::parse($value)->format('Y-m-d'),
            'pct' => $value.'%',
            default => (string) $value,
        };
    }

    public function exportCsv()
    {
        [$def, $rows, $totals, $f] = $this->compute();
        $columns = $def->columns();

        return response()->streamDownload(function () use ($def, $rows, $totals, $f, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [$def::title(), $f->rangeLabel()]);
            fputcsv($out, array_column($columns, 'label'));
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($k) => self::plain($row[$k] ?? null, $columns[$k]['type']), array_keys($columns)));
            }
            if ($totals) {
                fputcsv($out, array_map(fn ($k) => array_key_exists($k, $totals) ? self::plain($totals[$k], $columns[$k]['type']) : ($k === array_key_first($columns) ? 'Total' : ''), array_keys($columns)));
            }
            fclose($out);
        }, $def::key().'-'.now()->format('Y-m-d').'.csv');
    }

    public function render()
    {
        [$def, $rows, $totals, $f] = $this->compute();
        $want = $def->filters();

        return view('livewire.reports.viewer', [
            'def' => $def,
            'rows' => $rows,
            'totals' => $totals,
            'summary' => $def->summary($rows, $totals),
            'filters' => $f,
            'want' => $want,
            'currency' => \App\Models\Setting::get('finance.currency', 'BDT'),
            'options' => [
                'project' => in_array('project', $want) ? Project::orderBy('project_code')->get(['id', 'project_code', 'name']) : collect(),
                'client' => in_array('client', $want) ? Client::orderBy('name')->get(['id', 'name']) : collect(),
                'vendor' => in_array('vendor', $want) ? Vendor::orderBy('company_name')->get(['id', 'company_name']) : collect(),
                'employee' => in_array('employee', $want) ? Employee::orderBy('name')->get(['id', 'name']) : collect(),
                'user' => in_array('user', $want) ? User::staff()->orderBy('name')->get(['id', 'name']) : collect(),
                'category' => in_array('category', $want) ? ExpenseCategory::orderBy('name')->get(['id', 'name']) : collect(),
                'method' => PaymentMethod::options(),
            ],
            'groupLabel' => ['accounts' => 'Accounts Report', 'crm' => 'CRM Report', 'all' => 'All Report'][$def::group()],
        ])->title($def::title());
    }
}
