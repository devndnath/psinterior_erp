<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Project aggregate — the project itself plus its milestones,
 * tasks and ad-hoc team roster — mirroring the Phase 2 pattern where
 * SiteVisitService owns its items and ClientService owns its contacts,
 * rather than splitting each sub-collection into its own service class.
 */
class ProjectService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $leadId = $data['lead_id'] ?? null;

            $data['project_code'] = $this->numbering->next('PRJ');
            $project = Project::create($data);

            if ($leadId) {
                // Stamps the lead's conversion trail (ARCHITECTURE.md §2:
                // leads.converted_project_id) — set once, here, never
                // reassigned to a different project afterwards.
                Lead::whereKey($leadId)->update(['converted_project_id' => $project->id]);
            }

            return $project;
        });
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }

    public function addMilestone(Project $project, array $data): ProjectMilestone
    {
        $milestone = $project->milestones()->create($data);

        $this->recalculateProgress($project);

        return $milestone;
    }

    public function updateMilestone(ProjectMilestone $milestone, array $data): ProjectMilestone
    {
        $milestone->update($data);
        $this->recalculateProgress($milestone->project);

        return $milestone->fresh();
    }

    public function deleteMilestone(ProjectMilestone $milestone): void
    {
        $project = $milestone->project;
        $milestone->delete();
        $this->recalculateProgress($project);
    }

    public function addTask(Project $project, array $data): ProjectTask
    {
        $data['task_no'] = $this->numbering->next('TSK');

        return $project->tasks()->create($data);
    }

    public function updateTask(ProjectTask $task, array $data): ProjectTask
    {
        $task->update($data);

        return $task->fresh();
    }

    public function deleteTask(ProjectTask $task): void
    {
        $task->delete();
    }

    public function addMember(Project $project, int $userId, ?string $roleOnProject = null): void
    {
        $project->members()->syncWithoutDetaching([
            $userId => ['role_on_project' => $roleOnProject],
        ]);
    }

    public function removeMember(Project $project, int $userId): void
    {
        $project->members()->detach($userId);
    }

    /**
     * The project's overall `progress` is a manual field until it has at
     * least one milestone, at which point it becomes the average of its
     * milestones' progress — so adding/updating/deleting a milestone keeps
     * the project-level number honest without staff re-entering it by hand.
     */
    protected function recalculateProgress(Project $project): void
    {
        $milestones = $project->milestones()->get(['progress']);

        if ($milestones->isEmpty()) {
            return;
        }

        $project->update(['progress' => (int) round($milestones->avg('progress'))]);
    }
}
