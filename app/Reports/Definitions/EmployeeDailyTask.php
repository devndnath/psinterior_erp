<?php

namespace App\Reports\Definitions;

use App\Enums\TaskStatus;
use App\Models\ProjectTask;
use App\Reports\Filters;
use App\Reports\Report;
use Illuminate\Support\Collection;

/** Project tasks due in the period, per person. */
class EmployeeDailyTask extends Report
{
    public static function key(): string { return 'employee-daily-task'; }
    public static function title(): string { return 'Employee Daily Task Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_projects'; }
    public static function description(): string { return 'Project tasks due in the period for each person — status, progress and anything overdue.'; }

    public function filters(): array { return ['dates', 'user', 'project']; }
    public function defaultRange(): string { return 'today'; }

    public function note(): ?string
    {
        return 'Tasks whose due date falls in the range (open tasks that are already overdue are included too), grouped by who they\'re assigned to.';
    }

    public function columns(): array
    {
        return [
            'person' => ['label' => 'Assigned to', 'type' => 'text'],
            'task' => ['label' => 'Task', 'type' => 'text'],
            'project' => ['label' => 'Project', 'type' => 'text'],
            'due' => ['label' => 'Due', 'type' => 'date'],
            'status' => ['label' => 'Status', 'type' => 'text'],
            'progress' => ['label' => 'Progress', 'type' => 'pct'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $open = [TaskStatus::Todo->value, TaskStatus::InProgress->value, TaskStatus::Review->value];
        $tasks = ProjectTask::query()
            ->where(fn ($q) => $f->dates($q, 'due_date')
                ->when($f->from, fn ($q) => $q->orWhere(fn ($q) => $q->whereDate('due_date', '<', $f->from->toDateString())->whereIn('status', $open))))
            ->when($f->user, fn ($q) => $q->where('assigned_to', $f->user))
            ->when($f->project, fn ($q) => $q->where('project_id', $f->project))
            ->with(['assignee:id,name', 'project:id,project_code,name'])
            ->orderBy('due_date')->get()
            ->sortBy(fn ($t) => ($t->assignee?->name ?? 'zzz').'|'.$t->due_date?->format('Y-m-d'));

        $today = now()->startOfDay();
        foreach ($tasks as $t) {
            $late = $t->due_date && $t->due_date->lt($today) && in_array($t->status->value, $open, true);
            yield [
                'person' => $t->assignee?->name ?? 'Unassigned',
                'task' => self::cell($t->name, route('projects.show', $t->project_id), $t->task_no),
                'project' => $t->project?->project_code ?? '—',
                'due' => $t->due_date,
                'status' => $t->status->label().($late ? ' · overdue' : ''),
                'progress' => (int) $t->progress,
            ];
        }
    }

    public function summary(Collection $rows, array $totals): array
    {
        return [
            ['label' => 'Tasks', 'value' => $rows->count(), 'type' => 'int'],
            ['label' => 'Done', 'value' => $rows->filter(fn ($r) => str_starts_with($r['status'], TaskStatus::Completed->label()))->count(), 'type' => 'int', 'tone' => 'green'],
            ['label' => 'Overdue', 'value' => $rows->filter(fn ($r) => str_ends_with($r['status'], 'overdue'))->count(), 'type' => 'int', 'tone' => 'red'],
            ['label' => 'People', 'value' => $rows->pluck('person')->unique()->count(), 'type' => 'int'],
        ];
    }
}
