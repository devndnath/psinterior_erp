<?php

namespace App\Livewire\Subcontractors;

use App\Models\Subcontractor;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Subcontractor $subcontractor;

    public function mount(Subcontractor $subcontractor): void
    {
        Gate::authorize('view', $subcontractor);

        $this->subcontractor = $subcontractor;
    }

    public function deleteSubcontractor(): void
    {
        Gate::authorize('delete', $this->subcontractor);

        $name = $this->subcontractor->name;
        $this->subcontractor->delete();

        $this->dispatch('toast', message: "Subcontractor \"{$name}\" deleted.", type: 'success');
        $this->redirectRoute('subcontractors.index', navigate: true);
    }

    #[Title('Subcontractor')]
    public function render()
    {
        return view('livewire.subcontractors.show', [
            'assignments' => $this->subcontractor->assignments()->with('project')->latest()->get(),
        ]);
    }
}
