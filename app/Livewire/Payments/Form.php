<?php

namespace App\Livewire\Payments;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Records a payment received from a client. An optional `?invoice=`
 * query string (from an Invoice's "Record Payment" link) pre-selects
 * both the invoice and its project — mirrors how ChangeOrders/Form reads
 * `?project=`.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public string $project_id = '';
    public string $invoice_id = '';
    public string $amount = '';
    public string $method = 'bank';
    public string $date = '';
    public string $reference = '';
    public string $account = '';
    public string $notes = '';

    public function mount(): void
    {
        Gate::authorize('create', Payment::class);

        $this->date = now()->toDateString();

        $invoiceId = request()->query('invoice');

        if ($invoiceId && ($invoice = Invoice::find($invoiceId))) {
            $this->invoice_id = (string) $invoice->id;
            $this->project_id = (string) $invoice->project_id;

            return;
        }

        $projectId = request()->query('project');

        if ($projectId && Project::whereKey($projectId)->exists()) {
            $this->project_id = (string) $projectId;
        }
    }

    public function updatedProjectId(): void
    {
        // Dropping the project invalidates any previously selected
        // invoice that belonged to the old project.
        if ($this->invoice_id && ! Invoice::where('id', $this->invoice_id)->where('project_id', $this->project_id)->exists()) {
            $this->invoice_id = '';
        }
    }

    /**
     * Not a Livewire computed property (this codebase doesn't use the
     * `#[Computed]` attribute anywhere else) — just a plain helper called
     * from render().
     */
    protected function availableInvoices()
    {
        if (! $this->project_id) {
            return collect();
        }

        return Invoice::where('project_id', $this->project_id)
            ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Draft->value])
            ->get(['id', 'invoice_no', 'grand_total', 'paid_amount'])
            ->filter(fn ($invoice) => $invoice->outstandingAmount() > 0 || (string) $invoice->id === $this->invoice_id);
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'in:'.implode(',', array_column(PaymentMethod::cases(), 'value'))],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'account' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(PaymentService $service): void
    {
        $data = $this->validate();

        $project = Project::findOrFail($data['project_id']);

        $payment = $service->create([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'invoice_id' => $data['invoice_id'] ?: null,
            'date' => $data['date'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?: null,
            'account' => $data['account'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);

        $this->dispatch('toast', message: "Payment {$payment->payment_no} recorded.", type: 'success');
        $this->redirectRoute('payments.show', ['payment' => $payment->id], navigate: true);
    }

    #[Title('Record Payment')]
    public function render()
    {
        return view('livewire.payments.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'methods' => PaymentMethod::options(),
            'invoices' => $this->availableInvoices(),
        ]);
    }
}
