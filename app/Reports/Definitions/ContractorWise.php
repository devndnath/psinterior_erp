<?php

namespace App\Reports\Definitions;

use App\Models\Subcontractor;
use App\Reports\Filters;
use App\Reports\Report;

/** Each contractor's jobs: agreed, work done, paid and due. */
class ContractorWise extends Report
{
    public static function key(): string { return 'contractor-wise'; }
    public static function title(): string { return 'Contractor Wise Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_subcontractors'; }
    public static function description(): string { return 'Each contractor — jobs, agreed amount, work done, paid and still due.'; }

    public function filters(): array { return []; }
    public function defaultRange(): string { return 'all'; }

    public function note(): ?string
    {
        return 'Agreed = sum of each job\'s rate. Work done = rate × progress. Due = work done − paid, never below zero per job.';
    }

    public function columns(): array
    {
        return [
            'contractor' => ['label' => 'Contractor', 'type' => 'text'],
            'trade' => ['label' => 'Trade', 'type' => 'text'],
            'jobs' => ['label' => 'Jobs', 'type' => 'int', 'total' => true],
            'agreed' => ['label' => 'Agreed', 'type' => 'money', 'total' => true],
            'done' => ['label' => 'Work done', 'type' => 'money', 'total' => true],
            'paid' => ['label' => 'Paid', 'type' => 'money', 'total' => true],
            'due' => ['label' => 'Due', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $subs = Subcontractor::query()->orderBy('name')
            ->with(['assignments' => fn ($q) => $q->withSum('payments', 'amount')])->get();

        foreach ($subs as $s) {
            $jobs = $s->assignments;
            if ($jobs->isEmpty()) {
                continue;
            }
            yield [
                'contractor' => self::cell($s->name, route('subcontractors.show', $s), $s->phone),
                'trade' => $s->trade?->label() ?? '—',
                'jobs' => $jobs->count(),
                'agreed' => (float) $jobs->sum('rate'),
                'done' => (float) $jobs->sum(fn ($a) => (float) $a->rate * $a->progress / 100),
                'paid' => (float) $jobs->sum('payments_sum_amount'),
                'due' => (float) $jobs->sum(fn ($a) => max(0, (float) $a->rate * $a->progress / 100 - (float) $a->payments_sum_amount)),
            ];
        }
    }
}
