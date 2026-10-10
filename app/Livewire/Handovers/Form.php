<?php

namespace App\Livewire\Handovers;

use App\Models\Handover;
use App\Models\Project;
use App\Services\HandoverService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Always reached from a Project's Show page (`?project={id}`) — no
 * Index, mirroring VendorQuotations/Form's context-only access pattern,
 * since a handover is one-per-project.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public Project $project;

    public string $handover_date = '';
    public string $notes = '';

    public function mount(): void
    {
        Gate::authorize('create', Handover::class);

        $this->project = Project::findOrFail(request()->query('project'));
        $this->handover_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'handover_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(HandoverService $service): void
    {
        $data = $this->validate();

        try {
            $handover = $service->create($this->project, $data);
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: 'Handover recorded.', type: 'success');
        $this->redirectRoute('handovers.show', ['handover' => $handover->id], navigate: true);
    }

    #[Title('Record Handover')]
    public function render()
    {
        return view('livewire.handovers.form');
    }
}
