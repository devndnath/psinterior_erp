<?php

namespace App\Livewire\Client\ChangeOrders;

use App\Enums\ApprovalDecision;
use App\Models\ChangeOrder;
use App\Services\ChangeOrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Calls
 * ChangeOrderService::recordClientDecision() directly once ownership
 * is confirmed — never through ChangeOrderPolicy.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public ChangeOrder $changeOrder;

    public string $decision = '';

    public function mount(ChangeOrder $changeOrder): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($changeOrder->project && $changeOrder->project->client_id === $client->id, 403);

        $this->changeOrder = $changeOrder;
    }

    public function recordDecision(ChangeOrderService $service): void
    {
        $this->validate([
            'decision' => ['required', 'string', 'in:'.ApprovalDecision::Approved->value.','.ApprovalDecision::Rejected->value],
        ]);

        $this->changeOrder = $service->recordClientDecision($this->changeOrder, $this->decision);
        $this->reset('decision');

        $this->dispatch('toast', message: 'Your decision has been recorded.', type: 'success');
    }

    #[Title('Change Order')]
    public function render()
    {
        $this->changeOrder->load(['project', 'items']);

        return view('livewire.client.change-orders.show', [
            'decisionOptions' => [
                ApprovalDecision::Approved->value => ApprovalDecision::Approved->label(),
                ApprovalDecision::Rejected->value => ApprovalDecision::Rejected->label(),
            ],
        ]);
    }
}
