<?php

namespace App\Reports\Definitions;

use App\Enums\InvoiceType;
use App\Models\Payment;
use App\Reports\Filters;
use App\Reports\Report;
use Illuminate\Support\Collection;

/** Profit per project (and design sales) — received less spent. */
class ProfitReport extends Report
{
    use Concerns;

    public static function key(): string { return 'profit'; }
    public static function title(): string { return 'Profit Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'Profit per project — received against expenses — plus design sales, with the expected profit on the full contract.'; }

    public function filters(): array { return ['dates', 'project', 'client']; }
    public function defaultRange(): string { return 'all'; }

    public function note(): ?string
    {
        return 'Profit so far = received − expenses. Expected profit = contract value − expenses. Design sales have no project expenses, so all of it counts as profit. Office expenses are not charged to projects (see the Income Statement for the whole business).';
    }

    public function columns(): array
    {
        return [
            'project' => ['label' => 'Project', 'type' => 'text'],
            'contract' => ['label' => 'Contract value', 'type' => 'money', 'total' => true],
            'received' => ['label' => 'Received', 'type' => 'money', 'total' => true],
            'spent' => ['label' => 'Expenses', 'type' => 'money', 'total' => true],
            'profit' => ['label' => 'Profit so far', 'type' => 'money', 'total' => true],
            'margin' => ['label' => 'Margin', 'type' => 'pct'],
            'expected' => ['label' => 'Expected profit', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $projects = $this->projectsFor($f);
        $m = $this->moneyByProject($projects->pluck('id'), $f);

        foreach ($projects as $p) {
            $received = (float) ($m['received'][$p->id] ?? 0);
            $spent = (float) ($m['spent'][$p->id] ?? 0);
            $contract = (float) ($p->contract_value ?? 0);
            yield [
                'project' => self::cell($p->project_code, route('projects.show', $p), $p->client?->name),
                'contract' => $contract,
                'received' => $received,
                'spent' => $spent,
                'profit' => $received - $spent,
                'margin' => $received > 0 ? round(($received - $spent) / $received * 100, 1) : null,
                'expected' => $contract > 0 ? $contract - $spent : null,
            ];
        }

        if (! $f->project) {
            $design = (float) $f->dates(Payment::query(), 'date')
                ->when($f->client, fn ($q) => $q->where('client_id', $f->client))
                ->where(fn ($q) => $q->whereNull('project_id')->orWhereHas('invoice', fn ($i) => $i->where('type', InvoiceType::Design->value)))
                ->sum('amount');
            if ($design > 0) {
                yield ['project' => self::cell('Design sales', route('designs.index', ['type' => 'sold']), 'sold without a project'),
                    'contract' => 0, 'received' => $design, 'spent' => 0, 'profit' => $design, 'margin' => 100, 'expected' => null];
            }
        }
    }

    public function summary(Collection $rows, array $totals): array
    {
        $received = $totals['received'] ?? 0;

        return [
            ['label' => 'Received', 'value' => $received, 'type' => 'money', 'tone' => 'green'],
            ['label' => 'Expenses', 'value' => $totals['spent'] ?? 0, 'type' => 'money', 'tone' => 'gold'],
            ['label' => 'Profit so far', 'value' => $totals['profit'] ?? 0, 'type' => 'money', 'tone' => ($totals['profit'] ?? 0) < 0 ? 'red' : 'dark'],
            ['label' => 'Margin', 'value' => $received > 0 ? round(($totals['profit'] ?? 0) / $received * 100, 1) : 0, 'type' => 'pct'],
        ];
    }
}
