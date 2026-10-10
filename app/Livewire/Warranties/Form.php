<?php

namespace App\Livewire\Warranties;

use App\Models\Project;
use App\Models\Warranty;
use App\Services\WarrantyService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Warranty $warranty = null;

    public string $project_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $terms = '';

    public function mount(?Warranty $warranty = null): void
    {
        $this->warranty = $warranty?->exists ? $warranty : null;

        Gate::authorize($this->warranty ? 'update' : 'create', $this->warranty ?? Warranty::class);

        if ($this->warranty) {
            $this->fill([
                'project_id' => (string) $this->warranty->project_id,
                'start_date' => $this->warranty->start_date->toDateString(),
                'end_date' => $this->warranty->end_date->toDateString(),
                'terms' => (string) $this->warranty->terms,
            ]);
        } else {
            $projectId = request()->query('project');

            if ($projectId && Project::whereKey($projectId)->exists()) {
                $this->project_id = (string) $projectId;
            }

            $this->start_date = now()->toDateString();
            $this->end_date = now()->addYear()->toDateString();
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'terms' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save(WarrantyService $service): void
    {
        $data = $this->validate();

        $warrantyData = [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'terms' => $data['terms'] ?: null,
        ];

        if ($this->warranty) {
            $warranty = $service->update($this->warranty, $warrantyData);
            $message = 'Warranty updated.';
        } else {
            $project = Project::findOrFail($data['project_id']);
            $warranty = $service->create($project, $warrantyData);
            $message = 'Warranty created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('warranties.show', ['warranty' => $warranty->id], navigate: true);
    }

    #[Title('Warranty')]
    public function render()
    {
        return view('livewire.warranties.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
