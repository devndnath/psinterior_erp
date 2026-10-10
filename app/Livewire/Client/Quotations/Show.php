<?php

namespace App\Livewire\Client\Quotations;

use App\Enums\ApprovalDecision;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Calls QuotationService::changeStatus()
 * directly once ownership is confirmed — the same mapping
 * Quotations\Show::recordDecision() uses on staff's side, never
 * through QuotationPolicy.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public Quotation $quotation;

    public string $decision = '';

    public function mount(Quotation $quotation): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($quotation->client_id === $client->id, 403);

        $this->quotation = $quotation;
    }

    public function recordDecision(QuotationService $service): void
    {
        $this->validate([
            'decision' => ['required', 'string', 'in:'.implode(',', array_column(ApprovalDecision::cases(), 'value'))],
        ]);

        $status = match ($this->decision) {
            ApprovalDecision::Approved->value => QuotationStatus::Approved->value,
            ApprovalDecision::Rejected->value => QuotationStatus::Rejected->value,
            default => QuotationStatus::Negotiation->value,
        };

        $this->quotation = $service->changeStatus($this->quotation, $status);
        $this->reset('decision');

        $this->dispatch('toast', message: 'Your decision has been recorded.', type: 'success');
    }

    #[Title('Quotation')]
    public function render()
    {
        $this->quotation->load(['project', 'items']);

        return view('livewire.client.quotations.show', [
            'decisionOptions' => ApprovalDecision::options(),
        ]);
    }
}
