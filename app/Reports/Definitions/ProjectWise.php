<?php

namespace App\Reports\Definitions;

use App\Reports\Filters;
use App\Reports\Report;

/** Each project's money: budget, invoiced, received, spent, balance, due. */
class ProjectWise extends Report
{
    use Concerns;

    public static function key(): string { return 'project-wise'; }
    public static function title(): string { return 'Project Wise Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'Each project\'s budget, invoiced, received, expenses, balance in hand and amount still due.'; }

    public function filters(): array { return ['dates', 'project', 'client']; }
    public function defaultRange(): string { return 'all'; }

    public function note(): ?string
    {
        return 'Budget = contract value (or estimated budget before a contract). Balance = received − expenses. Due = invoiced − received. The date range applies to invoices, payments and expenses.';
    }

    public function columns(): array
    {
        return [
            'project' => ['label' => 'Project', 'type' => 'text'],
            'status' => ['label' => 'Status', 'type' => 'text'],
            'budget' => ['label' => 'Budget', 'type' => 'money', 'total' => true],
            'invoiced' => ['label' => 'Invoiced', 'type' => 'money', 'total' => true],
            'received' => ['label' => 'Received', 'type' => 'money', 'total' => true],
            'spent' => ['label' => 'Expenses', 'type' => 'money', 'total' => true],
            'balance' => ['label' => 'Balance', 'type' => 'money', 'total' => true],
            'due' => ['label' => 'Due', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $projects = $this->projectsFor($f);
        $m = $this->moneyByProject($projects->pluck('id'), $f);

        foreach ($projects as $p) {
            $received = (float) ($m['received'][$p->id] ?? 0);
            $spent = (float) ($m['spent'][$p->id] ?? 0);
            $invoiced = (float) ($m['invoiced'][$p->id] ?? 0);
            yield [
                'project' => self::cell($p->project_code, route('projects.show', $p), $p->client?->name),
                'status' => $p->status->label(),
                'budget' => self::budgetOf($p),
                'invoiced' => $invoiced,
                'received' => $received,
                'spent' => $spent,
                'balance' => $received - $spent,
                'due' => max(0, $invoiced - $received),
            ];
        }
    }
}
