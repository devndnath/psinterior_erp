<?php

namespace App\Livewire\Handovers;

use App\Models\Handover;
use App\Services\HandoverService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Handover $handover;

    public function mount(Handover $handover): void
    {
        Gate::authorize('view', $handover);

        $this->handover = $handover;
    }

    public function confirmByClient(HandoverService $service): void
    {
        Gate::authorize('confirmByClient', $this->handover);

        $this->handover = $service->confirmByClient($this->handover);

        $this->dispatch('toast', message: 'Client confirmation recorded.', type: 'success');
    }

    #[Title('Handover')]
    public function render()
    {
        $this->handover->load('project');

        return view('livewire.handovers.show');
    }
}
