<?php

namespace App\Livewire\Warranties;

use App\Models\Warranty;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Warranty $warranty;

    public function mount(Warranty $warranty): void
    {
        Gate::authorize('view', $warranty);

        $this->warranty = $warranty;
    }

    public function deleteWarranty(): void
    {
        Gate::authorize('delete', $this->warranty);

        $this->warranty->delete();

        $this->dispatch('toast', message: 'Warranty deleted.', type: 'success');
        $this->redirectRoute('warranties.index', navigate: true);
    }

    #[Title('Warranty')]
    public function render()
    {
        $this->warranty->load(['project', 'serviceRequests']);

        return view('livewire.warranties.show');
    }
}
