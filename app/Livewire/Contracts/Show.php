<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Services\ContractService;
use App\Services\PaymentScheduleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Contract $contract;

    public bool $showScheduleForm = false;

    public ?int $editingLineId = null;

    public string $scheduleLabel = '';

    public string $scheduleMilestoneId = '';

    public string $schedulePercentage = '';

    public string $scheduleAmount = '';

    public string $scheduleDueDate = '';

    public string $scheduleNotes = '';

    public ?int $linkingLineId = null;

    public string $linkInvoiceId = '';

    public function mount(Contract $contract): void
    {
        Gate::authorize('view', $contract);

        $this->contract = $contract;
    }

    public function toggleScheduleForm(): void
    {
        Gate::authorize('update', $this->contract);

        $this->showScheduleForm = ! $this->showScheduleForm;
        $this->resetScheduleForm();
    }

    public function editScheduleLine(int $lineId): void
    {
        Gate::authorize('update', $this->contract);

        $line = $this->contract->scheduleLines()->whereKey($lineId)->firstOrFail();

        $this->editingLineId = $line->id;
        $this->showScheduleForm = true;
        $this->scheduleLabel = $line->label;
        $this->scheduleMilestoneId = (string) $line->milestone_id;
        $this->schedulePercentage = (string) $line->percentage;
        $this->scheduleAmount = (string) $line->amount;
        $this->scheduleDueDate = $line->due_date?->toDateString() ?? '';
        $this->scheduleNotes = (string) $line->notes;
    }

    protected function resetScheduleForm(): void
    {
        $this->reset([
            'editingLineId', 'scheduleLabel', 'scheduleMilestoneId',
            'schedulePercentage', 'scheduleAmount', 'scheduleDueDate', 'scheduleNotes',
        ]);
    }

    protected function scheduleRules(): array
    {
        return [
            'scheduleLabel' => ['required', 'string', 'max:150'],
            'scheduleMilestoneId' => ['nullable', 'exists:project_milestones,id'],
            'schedulePercentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'scheduleAmount' => ['nullable', 'numeric', 'min:0'],
            'scheduleDueDate' => ['nullable', 'date'],
            'scheduleNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function saveScheduleLine(PaymentScheduleService $service): void
    {
        Gate::authorize('update', $this->contract);

        $data = $this->validate($this->scheduleRules());

        $lineData = [
            'milestone_id' => $data['scheduleMilestoneId'] ?: null,
            'label' => $data['scheduleLabel'],
            'percentage' => $data['schedulePercentage'] !== '' ? $data['schedulePercentage'] : null,
            'amount' => $data['scheduleAmount'] !== '' ? $data['scheduleAmount'] : null,
            'due_date' => $data['scheduleDueDate'] ?: null,
            'notes' => $data['scheduleNotes'] ?: null,
        ];

        try {
            if ($this->editingLineId) {
                $line = $this->contract->scheduleLines()->whereKey($this->editingLineId)->firstOrFail();
                $service->updateLine($line, $lineData);
                $message = 'Installment updated.';
            } else {
                $service->addLine($this->contract, $lineData);
                $message = 'Installment added.';
            }
        } catch (ValidationException $e) {
            // $e->getMessage() is just the generic "The given data was
            // invalid." — the service's actual per-field message (the
            // amount-vs-contract-total overflow text, etc.) lives in
            // $e->errors(), keyed by whatever field name the service's
            // own ValidationException used (not necessarily a property on
            // this component), so pull the first real message out of it.
            $this->addError('scheduleAmount', collect($e->errors())->flatten()->first() ?? 'Could not save this installment.');

            return;
        }

        $this->contract = $this->contract->fresh();
        $this->showScheduleForm = false;
        $this->resetScheduleForm();

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function removeScheduleLine(int $lineId, PaymentScheduleService $service): void
    {
        Gate::authorize('update', $this->contract);

        $line = $this->contract->scheduleLines()->whereKey($lineId)->firstOrFail();

        try {
            $service->removeLine($line);
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?? 'Could not remove this installment.', type: 'error');

            return;
        }

        $this->contract = $this->contract->fresh();
        $this->dispatch('toast', message: 'Installment removed.', type: 'success');
    }

    public function openLinkInvoiceForm(int $lineId): void
    {
        Gate::authorize('update', $this->contract);

        $this->linkingLineId = $lineId;
        $this->linkInvoiceId = '';
    }

    public function cancelLinkInvoiceForm(): void
    {
        $this->reset(['linkingLineId', 'linkInvoiceId']);
    }

    public function linkInvoice(PaymentScheduleService $service): void
    {
        Gate::authorize('update', $this->contract);

        $this->validate(['linkInvoiceId' => ['required', 'exists:invoices,id']]);

        $line = $this->contract->scheduleLines()->whereKey($this->linkingLineId)->firstOrFail();
        $invoice = $this->contract->invoices()->whereKey($this->linkInvoiceId)->firstOrFail();

        try {
            $service->linkInvoice($line, $invoice);
        } catch (ValidationException $e) {
            $this->addError('linkInvoiceId', collect($e->errors())->flatten()->first() ?? 'Could not link that invoice.');

            return;
        }

        $this->contract = $this->contract->fresh();
        $this->reset(['linkingLineId', 'linkInvoiceId']);

        $this->dispatch('toast', message: 'Installment linked to invoice.', type: 'success');
    }

    public function unlinkInvoice(int $lineId, PaymentScheduleService $service): void
    {
        Gate::authorize('update', $this->contract);

        $line = $this->contract->scheduleLines()->whereKey($lineId)->firstOrFail();
        $service->unlinkInvoice($line);

        $this->contract = $this->contract->fresh();
        $this->dispatch('toast', message: 'Invoice unlinked.', type: 'success');
    }

    public function activate(ContractService $service): void
    {
        Gate::authorize('update', $this->contract);

        $this->contract = $service->activate($this->contract);

        $this->dispatch('toast', message: "Contract {$this->contract->contract_no} is now Active — project contract value updated.", type: 'success');
    }

    /**
     * See App\Http\Controllers\Pdf\InvoicePdfController's docblock — same
     * fix, same reason: a binary PDF can't safely travel through
     * Livewire's own JSON response, so this redirects (full browser
     * navigation, not `navigate: true`) to a plain GET route instead.
     */
    public function downloadPdf()
    {
        Gate::authorize('view', $this->contract);

        return $this->redirect(route('contracts.pdf', $this->contract));
    }

    public function deleteContract(): void
    {
        Gate::authorize('delete', $this->contract);

        $no = $this->contract->contract_no;
        $this->contract->delete();

        $this->dispatch('toast', message: "Contract {$no} deleted.", type: 'success');
        $this->redirectRoute('contracts.index', navigate: true);
    }

    #[Title('Contract')]
    public function render(PaymentScheduleService $service)
    {
        $this->contract->load([
            'project', 'client', 'quotation', 'items',
            'scheduleLines.milestone', 'scheduleLines.invoice',
            'invoices',
        ]);

        return view('livewire.contracts.show', [
            'scheduleSummary' => $service->summary($this->contract),
            'milestones' => $this->contract->project
                ? $this->contract->project->milestones()->orderBy('due_date')->get(['id', 'name', 'due_date'])
                : collect(),
        ]);
    }
}
