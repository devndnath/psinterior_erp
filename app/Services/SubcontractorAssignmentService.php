<?php

namespace App\Services;

use App\Models\Project;
use App\Models\SubcontractorAssignment;

class SubcontractorAssignmentService
{
    public function create(Project $project, array $data): SubcontractorAssignment
    {
        return $project->subcontractorAssignments()->create([
            'subcontractor_id' => $data['subcontractor_id'],
            'work_description' => $data['work_description'] ?? null,
            'rate' => $data['rate'] ?? 0,
            'progress' => $data['progress'] ?? 0,
        ]);
    }

    public function update(SubcontractorAssignment $assignment, array $data): SubcontractorAssignment
    {
        $assignment->update([
            'work_description' => $data['work_description'] ?? $assignment->work_description,
            'rate' => $data['rate'] ?? $assignment->rate,
        ]);

        return $assignment->fresh();
    }

    public function updateProgress(SubcontractorAssignment $assignment, int $progress): SubcontractorAssignment
    {
        if ($progress < 0 || $progress > 100) {
            throw new \InvalidArgumentException('Progress must be between 0 and 100.');
        }

        $assignment->update(['progress' => $progress]);

        return $assignment->fresh();
    }
}
