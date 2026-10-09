<?php

namespace App\Livewire\Quotations;

use App\Enums\QuotationItemType;
use App\Models\Boq;
use App\Models\Project;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Create/edit a quotation. On create, an optional `?boq=` query string
 * (from a BOQ's "Create quotation from this BOQ" link) pre-fills the
 * item rows from that BOQ's items. Editing a quotation that has already
 * left Draft is still allowed here — QuotationService::update() handles
 * snapshotting the prior state transparently (spec §78 rule 1).
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Quotation $quotation = null;

    public string $project_id = '';
    public string $boq_id = '';
    public string $discount = '0';
    public string $tax = '0';
    public string $valid_until = '';
    public string $terms = '';
    public string $notes = '';

    /** @var array<int, array{type:string,description:string,qty:string,unit:string,rate:string}> */
    public array $items = [];

    public function mount(?Quotation $quotation = null): void
    {
        $this->quotation = $quotation?->exists ? $quotation : null;

        Gate::authorize($this->quotation ? 'update' : 'create', $this->quotation ?? Quotation::class);

        if ($this->quotation) {
            $this->fill([
                'project_id' => (string) $this->quotation->project_id,
                'boq_id' => (string) $this->quotation->boq_id,
                'discount' => (string) $this->quotation->discount,
                'tax' => (string) $this->quotation->tax,
                'valid_until' => $this->quotation->valid_until?->format('Y-m-d') ?? '',
                'terms' => (string) $this->quotation->terms,
                'notes' => (string) $this->quotation->notes,
            ]);

            $this->items = $this->quotation->items->map(fn ($item) => [
                'type' => $item->type->value,
                'description' => $item->description,
                'qty' => (string) $item->qty,
                'unit' => (string) $item->unit,
                'rate' => (string) $item->rate,
            ])->all();

            return;
        }

        $projectId = request()->query('project');

        if ($projectId && ($project = Project::find($projectId))) {
            $this->project_id = (string) $project->id;
        }

        $boqId = request()->query('boq');

        if ($boqId && ($boq = Boq::with('items')->find($boqId))) {
            $this->boq_id = (string) $boq->id;
            $this->project_id = (string) $boq->project_id;

            $this->items = $boq->items->map(fn ($item) => [
                'type' => QuotationItemType::Item->value,
                'description' => $item->item.($item->description ? " — {$item->description}" : ''),
                'qty' => (string) $item->qty,
                'unit' => (string) $item->unit,
                'rate' => (string) $item->rate,
            ])->all();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['type' => QuotationItemType::Item->value, 'description' => '', 'qty' => '1', 'unit' => '', 'rate' => '0'];
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
            'discount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items.*.type' => ['required', 'string', 'in:'.implode(',', array_column(QuotationItemType::cases(), 'value'))],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(QuotationService $service): void
    {
        $data = $this->validate();

        $quotationData = [
            'discount' => $data['discount'],
            'tax' => $data['tax'],
            'valid_until' => $data['valid_until'] ?: null,
            'terms' => $data['terms'] ?: null,
            'notes' => $data['notes'] ?: null,
        ];

        if ($this->quotation) {
            $quotation = $service->update($this->quotation, $quotationData, $data['items'], Auth::user());
            $message = "Quotation {$quotation->quotation_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $boq = $this->boq_id ? Boq::find($this->boq_id) : null;
            $quotationData['client_id'] = $project->client_id;

            $quotation = $service->create($project, $quotationData, $data['items'], $boq);
            $message = "Quotation {$quotation->quotation_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('quotations.show', ['quotation' => $quotation->id], navigate: true);
    }

    #[Title('Quotation')]
    public function render()
    {
        return view('livewire.quotations.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'itemTypes' => QuotationItemType::options(),
        ]);
    }
}
