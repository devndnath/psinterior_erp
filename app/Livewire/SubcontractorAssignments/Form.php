<?php

namespace App\Livewire\SubcontractorAssignments;

use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\SubcontractorAssignment;
use App\Services\SubcontractorAssignmentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Context-only — reached via EITHER `?subcontractor={id}` (from a
 * Subcontractor's Show page) or `?project={id}` (from a Project's Show
 * page), the same dual-entry-point flexibility ChangeOrders/Form
 * already has for `?project=`. No Index — an assignment is always
 * viewed in the context of one of its two parents.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public string $subcontractor_id = '';
    public string $project_id = '';
    public string $work_description = '';
    public string $rate = '0';

    public function mount(): void
    {
        Gate::authorize('create', SubcontractorAssignment::class);

        $subcontractorId = request()->query('subcontractor');
        $projectId = request()->query('project');

        if ($subcontractorId && Subcontractor::whereKey($subcontractorId)->exists()) {
            $this->subcontractor_id = (string) $subcontractorId;
        }

        if ($projectId && Project::whereKey($projectId)->exists()) {
            $this->project_id = (string) $projectId;
        }
    }

    protected function rules(): array
    {
        return [
            'subcontractor_id' => ['required', 'exists:subcontractors,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'work_description' => ['nullable', 'string', 'max:2000'],
            'rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(SubcontractorAssignmentService $service): void
    {
        $data = $this->validate();

        $project = Project::findOrFail($data['project_id']);
        $assignment = $service->create($project, $data);

        $this->dispatch('toast', message: 'Assignment created.', type: 'success');
        $this->redirectRoute('subcontractor-assignments.show', ['subcontractorAssignment' => $assignment->id], navigate: true);
    }

    #[Title('New Subcontractor Assignment')]
    public function render()
    {
        return view('livewire.subcontractor-assignments.form', [
            'subcontractors' => Subcontractor::orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
