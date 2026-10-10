<?php

namespace App\Livewire\Inspections;

use App\Models\Inspection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Inspection $inspection;

    public function mount(Inspection $inspection): void
    {
        Gate::authorize('view', $inspection);

        $this->inspection = $inspection;
    }

    public function deleteInspection(): void
    {
        Gate::authorize('delete', $this->inspection);

        $this->inspection->delete();

        $this->dispatch('toast', message: 'Inspection deleted.', type: 'success');
        $this->redirectRoute('inspections.index', navigate: true);
    }

    #[Title('Inspection')]
    public function render()
    {
        $this->inspection->load(['project', 'inspector', 'items', 'snags']);

        return view('livewire.inspections.show');
    }
}
