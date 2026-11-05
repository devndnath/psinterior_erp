<?php

namespace App\Reports\Definitions;

use App\Enums\ProjectStatus;
use App\Models\Design;
use App\Models\Project;
use App\Models\User;
use App\Reports\Filters;
use App\Reports\Report;

/** Projects and designs per architect / designer. */
class ArchitectWise extends Report
{
    use Concerns;

    public static function key(): string { return 'architect-wise'; }
    public static function title(): string { return 'Architect Wise Report'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'Per architect / designer — projects they lead, how many are running or done, their value, and designs uploaded.'; }

    public function filters(): array { return ['user']; }
    public function defaultRange(): string { return 'all'; }

    public function note(): ?string
    {
        return 'An architect is the designer set on a project. Designs counted by who uploaded them. Project value = contract value (or estimated budget).';
    }

    public function columns(): array
    {
        return [
            'architect' => ['label' => 'Architect / designer', 'type' => 'text'],
            'projects' => ['label' => 'Projects', 'type' => 'int', 'total' => true],
            'running' => ['label' => 'Running', 'type' => 'int', 'total' => true],
            'completed' => ['label' => 'Completed', 'type' => 'int', 'total' => true],
            'value' => ['label' => 'Project value', 'type' => 'money', 'total' => true],
            'designs' => ['label' => 'Designs', 'type' => 'int', 'total' => true],
            'approved' => ['label' => 'Approved designs', 'type' => 'int', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $projects = Project::whereNotNull('designer_id')
            ->when($f->user, fn ($q) => $q->where('designer_id', $f->user))
            ->get(['id', 'designer_id', 'status', 'contract_value', 'estimated_budget'])->groupBy('designer_id');
        $designs = Design::whereNotNull('uploaded_by')
            ->when($f->user, fn ($q) => $q->where('uploaded_by', $f->user))
            ->get(['id', 'uploaded_by', 'status'])->groupBy('uploaded_by');

        $users = User::whereIn('id', $projects->keys()->merge($designs->keys())->unique())->orderBy('name')->get(['id', 'name']);

        foreach ($users as $u) {
            $mine = $projects[$u->id] ?? collect();
            $ds = $designs[$u->id] ?? collect();
            yield [
                'architect' => $u->name,
                'projects' => $mine->count(),
                'running' => $mine->filter(fn ($p) => ! in_array($p->status, [ProjectStatus::Completed, ProjectStatus::Cancelled], true))->count(),
                'completed' => $mine->where('status', ProjectStatus::Completed)->count(),
                'value' => (float) $mine->sum(fn ($p) => self::budgetOf($p)),
                'designs' => $ds->count(),
                'approved' => $ds->where('status', \App\Enums\DesignStatus::Approved)->count(),
            ];
        }
    }
}
