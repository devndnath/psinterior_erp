<?php

namespace App\Livewire\ChangeOrders;

use App\Enums\ApprovalDecision;
use App\Models\ChangeOrder;
use App\Services\ChangeOrderService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public ChangeOrder $changeOrder;

    public bool $showDecisionForm = false;
    public string $decision = '';

    public function mount(ChangeOrder $changeOrder): void
    {
        Gate::authorize('view', $changeOrder);

        $this->changeOrder = $changeOrder;
    }

    public function toggleDecisionForm(): void
    {
        Gate::authorize('recordDecision', $this->changeOrder);

        $this->showDecisionForm = ! $this->showDecisionForm;
        $this->reset(['decision']);
    }

    public function recordDecision(ChangeOrderService $service): void
    {
        Gate::authorize('recordDecision', $this->changeOrder);

        $this->validate([
            'decision' => ['required', 'string', 'in:'.implode(',', array_column(ApprovalDecision::cases(), 'value'))],
        ]);

        $this->changeOrder = $service->recordClientDecision($this->changeOrder, $this->decision);
        $this->showDecisionForm = false;

        $this->dispatch('toast', message: 'Client decision recorded.', type: 'success');
    }

    public function deleteChangeOrder(): void
    {
        Gate::authorize('delete', $this->changeOrder);

        $no = $this->changeOrder->co_no;
        $this->changeOrder->delete();

        $this->dispatch('toast', message: "Change order {$no} deleted.", type: 'success');
        $this->redirectRoute('change-orders.index', navigate: true);
    }

    #[Title('Change Order')]
    public function render()
    {
        $this->changeOrder->load(['project', 'items']);

        return view('livewire.change-orders.show', [
            'decisionOptions' => ApprovalDecision::options(),
        ]);
    }
}
