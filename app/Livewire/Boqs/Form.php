<?php

namespace App\Livewire\Boqs;

use App\Models\Boq;
use App\Models\Project;
use App\Services\BoqService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Create/edit a BOQ. A project's BOQs are versioned rather than edited
 * away once activated (BoqService), but while a BOQ is Draft this form
 * is how its item rows are built up — mirroring the dynamic-row pattern
 * SiteVisits\Form uses for rooms.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Boq $boq = null;

    public string $project_id = '';
    public string $discount = '0';
    public string $tax = '0';

    /** @var array<int, array{item:string,description:string,category:string,qty:string,unit:string,rate:string,labour_cost:string,material_cost:string,remarks:string}> */
    public array $items = [];

    public function mount(?Boq $boq = null): void
    {
        $this->boq = $boq?->exists ? $boq : null;

        Gate::authorize($this->boq ? 'update' : 'create', $this->boq ?? Boq::class);

        if ($this->boq) {
            $this->fill([
                'project_id' => (string) $this->boq->project_id,
                'discount' => (string) $this->boq->discount,
                'tax' => (string) $this->boq->tax,
            ]);

            $this->items = $this->boq->items->map(fn ($item) => [
                'item' => $item->item,
                'description' => (string) $item->description,
                'category' => (string) $item->category,
                'qty' => (string) $item->qty,
                'unit' => (string) $item->unit,
                'rate' => (string) $item->rate,
                'labour_cost' => (string) $item->labour_cost,
                'material_cost' => (string) $item->material_cost,
                'remarks' => (string) $item->remarks,
            ])->all();
        } else {
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
        $this->items[] = [
            'item' => '', 'description' => '', 'category' => '', 'qty' => '1',
            'unit' => '', 'rate' => '0', 'labour_cost' => '0', 'material_cost' => '0', 'remarks' => '',
        ];
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
            'items.*.item' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.labour_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.material_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(BoqService $service): void
    {
        $data = $this->validate();

        $items = array_map(fn ($item) => [
            'item' => $item['item'],
            'description' => $item['description'] ?: null,
            'category' => $item['category'] ?: null,
            'qty' => $item['qty'],
            'unit' => $item['unit'] ?: null,
            'rate' => $item['rate'],
            'labour_cost' => $item['labour_cost'] ?: 0,
            'material_cost' => $item['material_cost'] ?: 0,
            'remarks' => $item['remarks'] ?: null,
        ], $data['items']);

        $boqData = ['discount' => $data['discount'], 'tax' => $data['tax']];

        if ($this->boq) {
            $boq = $service->update($this->boq, $boqData, $items);
            $message = "BOQ {$boq->boq_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $boq = $service->create($project, $boqData, $items);
            $message = "BOQ {$boq->boq_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('boqs.show', ['boq' => $boq->id], navigate: true);
    }

    #[Title('BOQ')]
    public function render()
    {
        return view('livewire.boqs.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
