<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceType;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Create/edit an invoice. The client is always derived from the chosen
 * project (never picked independently) — mirrors how Quotations/Form
 * sets client_id from the project on save.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Invoice $invoice = null;

    public string $project_id = '';
    public string $contract_id = '';
    public string $type = 'progress';
    public string $invoice_date = '';
    public string $due_date = '';
    public string $discount = '0';
    public string $tax = '0';
    public string $notes = '';

    /** @var array<int, array{description:string,qty:string,rate:string}> */
    public array $items = [];

    public function mount(?Invoice $invoice = null): void
    {
        $this->invoice = $invoice?->exists ? $invoice : null;

        Gate::authorize($this->invoice ? 'update' : 'create', $this->invoice ?? Invoice::class);

        if ($this->invoice) {
            $this->fill([
                'project_id' => (string) $this->invoice->project_id,
                'contract_id' => (string) $this->invoice->contract_id,
                'type' => $this->invoice->type->value,
                'invoice_date' => $this->invoice->invoice_date?->format('Y-m-d') ?? '',
                'due_date' => $this->invoice->due_date?->format('Y-m-d') ?? '',
                'discount' => (string) $this->invoice->discount,
                'tax' => (string) $this->invoice->tax,
                'notes' => (string) $this->invoice->notes,
            ]);

            $this->items = $this->invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'qty' => (string) $item->qty,
                'rate' => (string) $item->rate,
            ])->all();
        } else {
            $this->invoice_date = now()->toDateString();

            $projectId = request()->query('project');

            if ($projectId && Project::whereKey($projectId)->exists()) {
                $this->project_id = (string) $projectId;
            }
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'qty' => '1', 'rate' => '0'];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(InvoiceType::cases(), 'value'))],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(InvoiceService $service): void
    {
        $data = $this->validate();

        $invoiceData = [
            'contract_id' => $data['contract_id'] ?: null,
            'type' => $data['type'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?: null,
            'discount' => $data['discount'],
            'tax' => $data['tax'],
            'notes' => $data['notes'] ?: null,
        ];

        if ($this->invoice) {
            $invoice = $service->update($this->invoice, $invoiceData, $data['items']);
            $message = "Invoice {$invoice->invoice_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $invoiceData['client_id'] = $project->client_id;

            $invoice = $service->create($project, $invoiceData, $data['items']);
            $message = "Invoice {$invoice->invoice_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('invoices.show', ['invoice' => $invoice->id], navigate: true);
    }

    #[Title('Invoice')]
    public function render()
    {
        $contracts = $this->project_id
            ? Contract::where('project_id', $this->project_id)->get(['id', 'contract_no'])
            : collect();

        return view('livewire.invoices.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'contracts' => $contracts,
            'types' => InvoiceType::options(),
        ]);
    }
}
