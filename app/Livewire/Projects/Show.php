<?php

namespace App\Livewire\Projects;

use App\Enums\LeadPriority;
use App\Enums\MilestoneStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use App\Services\ProfitabilityService;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Project workspace: details, milestones, tasks and the ad-hoc team
 * roster all live on this one page (mirroring Client Show's 360 layout),
 * rather than as separate CRUD pages — a project's sub-items are always
 * viewed in the context of the project, never browsed standalone.
 */
#[Layout('layouts.admin')]
class Show extends Component
{
    public Project $project;

    public bool $showMilestoneForm = false;
    public string $milestone_name = '';
    public string $milestone_due_date = '';
    public string $milestone_assigned_to = '';
    public string $milestone_priority = 'medium';

    public bool $showTaskForm = false;
    public string $task_name = '';
    public string $task_milestone_id = '';
    public string $task_assigned_to = '';
    public string $task_due_date = '';
    public string $task_priority = 'medium';

    public bool $showMemberForm = false;
    public string $member_user_id = '';
    public string $member_role = '';

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->project = $project;
    }

    public function addMilestone(ProjectService $service): void
    {
        Gate::authorize('update', $this->project);

        $data = $this->validate([
            'milestone_name' => ['required', 'string', 'max:255'],
            'milestone_due_date' => ['nullable', 'date'],
            'milestone_assigned_to' => ['nullable', 'exists:users,id'],
            'milestone_priority' => ['required', 'string', 'in:'.implode(',', array_column(LeadPriority::cases(), 'value'))],
        ]);

        $service->addMilestone($this->project, [
            'name' => $data['milestone_name'],
            'due_date' => $data['milestone_due_date'] ?: null,
            'assigned_to' => $data['milestone_assigned_to'] ?: null,
            'priority' => $data['milestone_priority'],
        ]);

        $this->reset(['milestone_name', 'milestone_due_date', 'milestone_assigned_to', 'showMilestoneForm']);
        $this->project->refresh();

        $this->dispatch('toast', message: 'Milestone added.', type: 'success');
    }

    public function updateMilestoneStatus(ProjectService $service, int $milestoneId, string $status): void
    {
        Gate::authorize('update', $this->project);

        $milestone = $this->project->milestones()->findOrFail($milestoneId);
        $progress = $status === MilestoneStatus::Completed->value ? 100 : $milestone->progress;

        $service->updateMilestone($milestone, ['status' => $status, 'progress' => $progress]);
        $this->project->refresh();
    }

    public function removeMilestone(ProjectService $service, int $milestoneId): void
    {
        Gate::authorize('update', $this->project);

        $service->deleteMilestone($this->project->milestones()->findOrFail($milestoneId));
        $this->project->refresh();

        $this->dispatch('toast', message: 'Milestone removed.', type: 'success');
    }

    public function addTask(ProjectService $service): void
    {
        Gate::authorize('update', $this->project);

        $data = $this->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_milestone_id' => ['nullable', 'exists:project_milestones,id'],
            'task_assigned_to' => ['nullable', 'exists:users,id'],
            'task_due_date' => ['nullable', 'date'],
            'task_priority' => ['required', 'string', 'in:'.implode(',', array_column(LeadPriority::cases(), 'value'))],
        ]);

        $service->addTask($this->project, [
            'name' => $data['task_name'],
            'milestone_id' => $data['task_milestone_id'] ?: null,
            'assigned_to' => $data['task_assigned_to'] ?: null,
            'due_date' => $data['task_due_date'] ?: null,
            'priority' => $data['task_priority'],
        ]);

        $this->reset(['task_name', 'task_milestone_id', 'task_assigned_to', 'task_due_date', 'showTaskForm']);
        $this->project->refresh();

        $this->dispatch('toast', message: 'Task added.', type: 'success');
    }

    public function updateTaskStatus(ProjectService $service, int $taskId, string $status): void
    {
        Gate::authorize('update', $this->project);

        $task = $this->project->tasks()->findOrFail($taskId);
        $progress = $status === TaskStatus::Completed->value ? 100 : $task->progress;

        $service->updateTask($task, ['status' => $status, 'progress' => $progress]);
    }

    public function removeTask(ProjectService $service, int $taskId): void
    {
        Gate::authorize('update', $this->project);

        $service->deleteTask($this->project->tasks()->findOrFail($taskId));

        $this->dispatch('toast', message: 'Task removed.', type: 'success');
    }

    public function addMember(ProjectService $service): void
    {
        Gate::authorize('update', $this->project);

        $data = $this->validate([
            'member_user_id' => ['required', 'exists:users,id'],
            'member_role' => ['nullable', 'string', 'max:255'],
        ]);

        $service->addMember($this->project, (int) $data['member_user_id'], $data['member_role'] ?: null);

        $this->reset(['member_user_id', 'member_role', 'showMemberForm']);
        $this->project->refresh();

        $this->dispatch('toast', message: 'Team member added.', type: 'success');
    }

    public function removeMember(ProjectService $service, int $userId): void
    {
        Gate::authorize('update', $this->project);

        $service->removeMember($this->project, $userId);
        $this->project->refresh();

        $this->dispatch('toast', message: 'Team member removed.', type: 'success');
    }

    #[Title('Project')]
    public function render(ProfitabilityService $profitability)
    {
        return view('livewire.projects.show', [
            'milestones' => $this->project->milestones()->orderBy('due_date')->get(),
            'tasks' => $this->project->tasks()->with('milestone')->orderBy('due_date')->get(),
            'siteVisits' => $this->project->siteVisits()->latest('visit_date')->get(),
            'designs' => Auth::user()->can('view_designs')
                ? $this->project->designs()->with('revisions')->latest()->get()
                : collect(),
            'boqs' => Auth::user()->can('view_boqs')
                ? $this->project->boqs()->latest('version')->get()
                : collect(),
            'quotations' => Auth::user()->can('view_quotations')
                ? $this->project->quotations()->latest()->get()
                : collect(),
            'contracts' => Auth::user()->can('view_contracts')
                ? $this->project->contracts()->latest()->get()
                : collect(),
            'changeOrders' => Auth::user()->can('view_change_orders')
                ? $this->project->changeOrders()->latest()->get()
                : collect(),
            'invoices' => Auth::user()->can('view_invoices')
                ? $this->project->invoices()->latest()->get()
                : collect(),
            'payments' => Auth::user()->can('view_payments')
                ? $this->project->payments()->latest('date')->get()
                : collect(),
            'expenses' => Auth::user()->can('view_expenses')
                ? $this->project->expenses()->latest('date')->get()
                : collect(),
            'materialRequisitions' => Auth::user()->can('view_material_requisitions')
                ? $this->project->materialRequisitions()->latest('date')->get()
                : collect(),
            'purchaseOrders' => Auth::user()->can('view_purchase_orders')
                ? $this->project->purchaseOrders()->latest()->get()
                : collect(),
            'subcontractorAssignments' => Auth::user()->can('view_subcontractors')
                ? $this->project->subcontractorAssignments()->with('subcontractor')->latest()->get()
                : collect(),
            'profitability' => Auth::user()->can('view_finance')
                ? $profitability->forProject($this->project)
                : null,
            'members' => $this->project->members,
            'staff' => User::staff()->orderBy('name')->get(['id', 'name']),
            'milestoneStatuses' => MilestoneStatus::options(),
            'taskStatuses' => TaskStatus::options(),
        ]);
    }
}
