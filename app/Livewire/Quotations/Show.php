<?php

namespace App\Livewire\Quotations;

use App\Enums\ApprovalDecision;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Quotation $quotation;

    public bool $showDecisionForm = false;
    public string $decision = '';
    public string $decisionNote = '';

    public function mount(Quotation $quotation): void
    {
        Gate::authorize('view', $quotation);

        $this->quotation = $quotation;
    }

    /**
     * Moves the quotation through the non-final statuses (Sent, Viewed,
     * Negotiation) — the final Approved/Rejected decision goes through
     * recordDecision() instead, which is gated by the distinct
     * `approve_quotation` ability.
     */
    public function changeStatus(QuotationService $service, string $status): void
    {
        Gate::authorize('update', $this->quotation);

        $this->quotation = $service->changeStatus($this->quotation, $status);

        $this->dispatch('toast', message: 'Status updated.', type: 'success');
    }

    public function toggleDecisionForm(): void
    {
        Gate::authorize('recordDecision', $this->quotation);

        $this->showDecisionForm = ! $this->showDecisionForm;
        $this->reset(['decision', 'decisionNote']);
    }

    public function recordDecision(QuotationService $service): void
    {
        Gate::authorize('recordDecision', $this->quotation);

        $this->validate([
            'decision' => ['required', 'string', 'in:'.implode(',', array_column(ApprovalDecision::cases(), 'value'))],
        ]);

        $status = match ($this->decision) {
            ApprovalDecision::Approved->value => QuotationStatus::Approved->value,
            ApprovalDecision::Rejected->value => QuotationStatus::Rejected->value,
            default => QuotationStatus::Negotiation->value,
        };

        $this->quotation = $service->changeStatus($this->quotation, $status);
        $this->showDecisionForm = false;

        $this->dispatch('toast', message: 'Client decision recorded.', type: 'success');
    }

    /**
     * See App\Http\Controllers\Pdf\InvoicePdfController's docblock — same
     * fix, same reason: a binary PDF can't safely travel through
     * Livewire's own JSON response, so this redirects (full browser
     * navigation, not `navigate: true`) to a plain GET route instead.
     */
    public function downloadPdf()
    {
        Gate::authorize('view', $this->quotation);

        return $this->redirect(route('quotations.pdf', $this->quotation));
    }

    public function deleteQuotation(): void
    {
        Gate::authorize('delete', $this->quotation);

        $no = $this->quotation->quotation_no;
        $this->quotation->delete();

        $this->dispatch('toast', message: "Quotation {$no} deleted.", type: 'success');
        $this->redirectRoute('quotations.index', navigate: true);
    }

    #[Title('Quotation')]
    public function render()
    {
        $this->quotation->load(['project.client', 'client', 'boq', 'items', 'versions', 'contracts']);

        return view('livewire.quotations.show', [
            'decisionOptions' => ApprovalDecision::options(),
            'statusOptions' => QuotationStatus::options(),
        ]);
    }
}
