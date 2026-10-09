<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Models\Project;
use App\Models\Quotation;
use App\Services\ContractService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Create/edit a contract. On create, an optional `?quotation=` query
 * string (from an approved Quotation's "Create Contract" link)
 * pre-fills the amount and item rows from that quotation
 * (ContractService::createFromQuotation).
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Contract $contract = null;

    public string $project_id = '';
    public string $quotation_id = '';
    public string $amount = '0';
    public string $start_date = '';
    public string $end_date = '';
    public string $payment_terms = '';
    public string $warranty_terms = '';
    public string $terms = '';

    /** @var array<int, array{description:string,amount:string}> */
    public array $items = [];

    public function mount(?Contract $contract = null): void
    {
        $this->contract = $contract?->exists ? $contract : null;

        Gate::authorize($this->contract ? 'update' : 'create', $this->contract ?? Contract::class);

        if ($this->contract) {
            $this->fill([
                'project_id' => (string) $this->contract->project_id,
                'quotation_id' => (string) $this->contract->quotation_id,
                'amount' => (string) $this->contract->amount,
                'start_date' => $this->contract->start_date?->format('Y-m-d') ?? '',
                'end_date' => $this->contract->end_date?->format('Y-m-d') ?? '',
                'payment_terms' => (string) $this->contract->payment_terms,
                'warranty_terms' => (string) $this->contract->warranty_terms,
                'terms' => (string) $this->contract->terms,
            ]);

            $this->items = $this->contract->items->map(fn ($item) => [
                'description' => $item->description,
                'amount' => (string) $item->amount,
            ])->all();

            return;
        }

        $projectId = request()->query('project');

        if ($projectId && ($project = Project::find($projectId))) {
            $this->project_id = (string) $project->id;
        }

        $quotationId = request()->query('quotation');

        if ($quotationId && ($quotation = Quotation::with('items')->find($quotationId))) {
            $this->quotation_id = (string) $quotation->id;
            $this->project_id = (string) $quotation->project_id;
            $this->amount = (string) $quotation->grand_total;

            $this->items = $quotation->items->map(fn ($item) => [
                'description' => $item->description,
                'amount' => (string) $item->amount,
            ])->all();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'amount' => '0'];
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
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'payment_terms' => ['nullable', 'string', 'max:2000'],
            'warranty_terms' => ['nullable', 'string', 'max:2000'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(ContractService $service): void
    {
        $data = $this->validate();

        $contractData = [
            'quotation_id' => $this->quotation_id ?: null,
            'amount' => $data['amount'],
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'payment_terms' => $data['payment_terms'] ?: null,
            'warranty_terms' => $data['warranty_terms'] ?: null,
            'terms' => $data['terms'] ?: null,
        ];

        if ($this->contract) {
            $contract = $service->update($this->contract, $contractData, $data['items']);
            $message = "Contract {$contract->contract_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $contract = $service->create($project, $contractData, $data['items']);
            $message = "Contract {$contract->contract_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('contracts.show', ['contract' => $contract->id], navigate: true);
    }

    #[Title('Contract')]
    public function render()
    {
        return view('livewire.contracts.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
