<?php

namespace App\Reports\Definitions;

use App\Reports\Filters;
use App\Reports\Report;

/** Estimated budget against what's actually been spent, per project. */
class EstimatedBudget extends Report
{
    use Concerns;

    public static function key(): string { return 'estimated-budget'; }
    public static function title(): string { return 'Estimated Budget Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'Each project\'s estimated budget against the contract value and actual spending — how much budget is left.'; }

    public function filters(): array { return ['project', 'client']; }
    public function defaultRange(): string { return 'all'; }

    public function note(): ?string
    {
        return 'Remaining = estimated budget − expenses so far; negative means over budget. Projects without an estimated budget show a dash.';
    }

    public function columns(): array
    {
        return [
            'project' => ['label' => 'Project', 'type' => 'text'],
            'status' => ['label' => 'Status', 'type' => 'text'],
            'estimate' => ['label' => 'Estimated budget', 'type' => 'money', 'total' => true],
            'contract' => ['label' => 'Contract value', 'type' => 'money', 'total' => true],
            'spent' => ['label' => 'Spent', 'type' => 'money', 'total' => true],
            'remaining' => ['label' => 'Remaining', 'type' => 'money', 'total' => true],
            'used' => ['label' => 'Used', 'type' => 'pct'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $projects = $this->projectsFor($f);
        $spent = $this->moneyByProject($projects->pluck('id'), new Filters)['spent'];

        foreach ($projects as $p) {
            $estimate = $p->estimated_budget !== null ? (float) $p->estimated_budget : null;
            $s = (float) ($spent[$p->id] ?? 0);
            yield [
                'project' => self::cell($p->project_code, route('projects.show', $p), $p->client?->name),
                'status' => $p->status->label(),
                'estimate' => $estimate,
                'contract' => $p->contract_value !== null ? (float) $p->contract_value : null,
                'spent' => $s,
                'remaining' => $estimate !== null ? $estimate - $s : null,
                'used' => $estimate ? round($s / $estimate * 100, 1) : null,
            ];
        }
    }
}
