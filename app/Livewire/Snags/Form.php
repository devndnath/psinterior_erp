<?php

namespace App\Livewire\Snags;

use App\Models\Inspection;
use App\Models\Project;
use App\Models\Snag;
use App\Services\SnagService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Snag $snag = null;
    public ?Inspection $inspection = null;

    public string $project_id = '';
    public string $area = '';
    public string $issue = '';
    public string $deadline = '';

    public function mount(?Snag $snag = null): void
    {
        $this->snag = $snag?->exists ? $snag : null;

        Gate::authorize($this->snag ? 'update' : 'create', $this->snag ?? Snag::class);

        if ($this->snag) {
            $this->fill([
                'project_id' => (string) $this->snag->project_id,
                'area' => (string) $this->snag->area,
                'issue' => (string) $this->snag->issue,
                'deadline' => (string) optional($this->snag->deadline)->toDateString(),
            ]);
        } else {
            $projectId = request()->query('project');
            $inspectionId = request()->query('inspection');

            if ($inspectionId && Inspection::whereKey($inspectionId)->exists()) {
                $this->inspection = Inspection::find($inspectionId);
                $projectId = $projectId ?: $this->inspection->project_id;
                $this->area = (string) $this->inspection->area;
            }

            if ($projectId && Project::whereKey($projectId)->exists()) {
                $this->project_id = (string) $projectId;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'area' => ['nullable', 'string', 'max:255'],
            'issue' => ['required', 'string', 'max:2000'],
            'deadline' => ['nullable', 'date'],
        ];
    }

    public function save(SnagService $service): void
    {
        $data = $this->validate();

        $snagData = [
            'area' => $data['area'] ?: null,
            'issue' => $data['issue'],
            'deadline' => $data['deadline'] ?: null,
        ];

        if ($this->snag) {
            $snag = $service->update($this->snag, $snagData);
            $message = "Snag {$snag->snag_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $snag = $service->create($project, $snagData, $this->inspection);
            $message = "Snag {$snag->snag_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('snags.show', ['snag' => $snag->id], navigate: true);
    }

    #[Title('Snag')]
    public function render()
    {
        return view('livewire.snags.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
