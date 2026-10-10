<?php

namespace App\Livewire\Client\Designs;

use App\Enums\ApprovalDecision;
use App\Models\Design;
use App\Services\DesignService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Calls DesignService::recordClientApproval()
 * directly (the same method staff used on the client's behalf in every
 * prior phase) once ownership is confirmed — never through DesignPolicy.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public Design $design;

    public string $decision = '';
    public string $comment = '';

    public function mount(Design $design): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($design->project && $design->project->client_id === $client->id, 403);

        $this->design = $design;
    }

    public function recordDecision(DesignService $service): void
    {
        $this->validate([
            'decision' => ['required', 'string', 'in:'.implode(',', array_column(ApprovalDecision::cases(), 'value'))],
        ]);

        $revision = $this->design->latestRevision();
        abort_unless($revision, 404, 'This design has no revision to decide on yet.');

        $service->recordClientApproval($revision, $this->decision, $this->comment ?: null, request()->ip());

        $this->design = $this->design->fresh();
        $this->reset(['decision', 'comment']);

        $this->dispatch('toast', message: 'Your decision has been recorded.', type: 'success');
    }

    #[Title('Design')]
    public function render()
    {
        $this->design->load(['project', 'revisions']);

        return view('livewire.client.designs.show', [
            'decisionOptions' => ApprovalDecision::options(),
        ]);
    }
}
