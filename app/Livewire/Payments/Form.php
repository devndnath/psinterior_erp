<?php

namespace App\Livewire\Payments;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Client;
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
    /** 'project' = received against a project; 'design' = for a design sold without a project. */
    public string $billing = 'project';
    public string $project_id = '';
    public string $client_id = '';
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
            $this->client_id = (string) $invoice->client_id;
            $this->billing = $invoice->project_id ? 'project' : 'design';

            return;
        }

        $projectId = request()->query('project');

        if ($projectId && Project::whereKey($projectId)->exists()) {
            $this->project_id = (string) $projectId;
        }
    }

    public function updatedBilling(): void
    {
        $this->project_id = '';
        $this->invoice_id = '';
    }

    public function updatedClientId(): void
    {
        if ($this->invoice_id && ! Invoice::where('id', $this->invoice_id)->whereNull('project_id')->where('client_id', $this->client_id)->exists()) {
            $this->invoice_id = '';
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
        if ($this->billing === 'design') {
            if (! $this->client_id) {
                return collect();
            }

            $query = Invoice::whereNull('project_id')->where('client_id', $this->client_id);
        } elseif ($this->project_id) {
            $query = Invoice::where('project_id', $this->project_id);
        } else {
            return collect();
        }

        return $query
            ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Draft->value])
            ->get(['id', 'invoice_no', 'grand_total', 'paid_amount'])
            ->filter(fn ($invoice) => $invoice->outstandingAmount() > 0 || (string) $invoice->id === $this->invoice_id);
    }

    protected function rules(): array
    {
        return [
            'billing' => ['required', 'in:project,design'],
            'project_id' => ['required_if:billing,project', 'nullable', 'exists:projects,id'],
            'client_id' => ['required_if:billing,design', 'nullable', 'exists:clients,id'],
            'invoice_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('invoices', 'id')
                    ->where(fn ($q) => $this->billing === 'design'
                        ? $q->whereNull('project_id')->where('client_id', $this->client_id ?: 0)
                        : $q->where('project_id', $this->project_id ?: 0))
                    ->where(fn ($q) => $q->where('status', '!=', \App\Enums\InvoiceStatus::Cancelled->value)),
            ],
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

        $project = $data['billing'] === 'project' ? Project::findOrFail($data['project_id']) : null;

        $payment = $service->create([
            'client_id' => $project ? $project->client_id : $data['client_id'],
            'project_id' => $project?->id,
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
            'clients' => Client::orderBy('name')->get(['id', 'name', 'client_no']),
            'methods' => PaymentMethod::options(),
            'invoices' => $this->availableInvoices(),
        ]);
    }
}
