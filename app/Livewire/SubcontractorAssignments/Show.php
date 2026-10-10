<?php

namespace App\Livewire\SubcontractorAssignments;

use App\Models\SubcontractorAssignment;
use App\Services\SubcontractorAssignmentService;
use App\Services\SubcontractorPaymentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Embeds two small, non-destructive inline forms directly on the Show
 * page — "Update Progress" and "Record Payment" — the same way
 * Warehouses/Show embeds an inline Opening-Stock form (Phase 7) rather
 * than giving either its own routed component. SubcontractorPayment has
 * no Form of its own for the same reason GoodsReceive has no edit: it's
 * a small, append-only action on its parent's page.
 */
#[Layout('layouts.admin')]
class Show extends Component
{
    public SubcontractorAssignment $subcontractorAssignment;

    public bool $showProgressForm = false;
    public string $progress = '0';

    public bool $showPaymentForm = false;
    public string $payment_date = '';
    public string $payment_amount = '';
    public string $payment_notes = '';

    public function mount(SubcontractorAssignment $subcontractorAssignment): void
    {
        Gate::authorize('view', $subcontractorAssignment);

        $this->subcontractorAssignment = $subcontractorAssignment;
        $this->progress = (string) $subcontractorAssignment->progress;
        $this->payment_date = now()->toDateString();
    }

    public function toggleProgressForm(): void
    {
        Gate::authorize('update', $this->subcontractorAssignment);

        $this->showProgressForm = ! $this->showProgressForm;
        $this->progress = (string) $this->subcontractorAssignment->progress;
    }

    public function updateProgress(SubcontractorAssignmentService $service): void
    {
        Gate::authorize('update', $this->subcontractorAssignment);

        $data = $this->validate(['progress' => ['required', 'integer', 'min:0', 'max:100']]);

        try {
            $this->subcontractorAssignment = $service->updateProgress($this->subcontractorAssignment, (int) $data['progress']);
            $this->showProgressForm = false;
            $this->dispatch('toast', message: 'Progress updated.', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function togglePaymentForm(): void
    {
        Gate::authorize('create', \App\Models\SubcontractorPayment::class);

        $this->showPaymentForm = ! $this->showPaymentForm;
        $this->reset(['payment_amount', 'payment_notes']);
        $this->payment_date = now()->toDateString();
    }

    public function recordPayment(SubcontractorPaymentService $service): void
    {
        Gate::authorize('create', \App\Models\SubcontractorPayment::class);

        $data = $this->validate([
            'payment_date' => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->create($this->subcontractorAssignment, [
            'date' => $data['payment_date'],
            'amount' => $data['payment_amount'],
            'notes' => $data['payment_notes'] ?: null,
        ]);

        $this->reset(['payment_amount', 'payment_notes', 'showPaymentForm']);
        $this->payment_date = now()->toDateString();

        $this->dispatch('toast', message: 'Payment recorded.', type: 'success');
    }

    public function deleteAssignment(): void
    {
        Gate::authorize('delete', $this->subcontractorAssignment);

        $subcontractorId = $this->subcontractorAssignment->subcontractor_id;
        $this->subcontractorAssignment->delete();

        $this->dispatch('toast', message: 'Assignment removed.', type: 'success');
        $this->redirectRoute('subcontractors.show', ['subcontractor' => $subcontractorId], navigate: true);
    }

    #[Title('Subcontractor Assignment')]
    public function render()
    {
        $this->subcontractorAssignment->load(['subcontractor', 'project', 'payments']);

        return view('livewire.subcontractor-assignments.show', [
            'totalPaid' => $this->subcontractorAssignment->totalPaid(),
        ]);
    }
}
