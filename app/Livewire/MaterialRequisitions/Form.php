<?php

namespace App\Livewire\MaterialRequisitions;

use App\Models\MaterialRequisition;
use App\Models\Product;
use App\Models\Project;
use App\Models\Warehouse;
use App\Services\MaterialRequisitionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?MaterialRequisition $materialRequisition = null;

    public string $project_id = '';
    public string $warehouse_id = '';
    public string $date = '';
    public string $purpose = '';

    /** @var array<int, array{product_id:string,quantity:string}> */
    public array $items = [];

    public function mount(?MaterialRequisition $materialRequisition = null): void
    {
        $this->materialRequisition = $materialRequisition?->exists ? $materialRequisition : null;

        Gate::authorize($this->materialRequisition ? 'update' : 'create', $this->materialRequisition ?? MaterialRequisition::class);

        if ($this->materialRequisition) {
            $this->fill([
                'project_id' => (string) $this->materialRequisition->project_id,
                'warehouse_id' => (string) $this->materialRequisition->warehouse_id,
                'date' => $this->materialRequisition->date->toDateString(),
                'purpose' => (string) $this->materialRequisition->purpose,
            ]);

            $this->items = $this->materialRequisition->items->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'quantity' => (string) $item->quantity,
            ])->all();
        } else {
            $this->date = now()->toDateString();

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
        $this->items[] = ['product_id' => '', 'quantity' => '1'];
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
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'date' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:2000'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function save(MaterialRequisitionService $service): void
    {
        $data = $this->validate();

        if ($this->materialRequisition) {
            $mr = $service->update($this->materialRequisition, [
                'warehouse_id' => $data['warehouse_id'] ?: null,
                'date' => $data['date'],
                'purpose' => $data['purpose'] ?: null,
            ], $data['items']);
            $message = "Material requisition {$mr->mr_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);

            $mr = $service->create($project, [
                'requested_by' => Auth::id(),
                'warehouse_id' => $data['warehouse_id'] ?: null,
                'date' => $data['date'],
                'purpose' => $data['purpose'] ?: null,
            ], $data['items']);
            $message = "Material requisition {$mr->mr_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('material-requisitions.show', ['materialRequisition' => $mr->id], navigate: true);
    }

    #[Title('Material Requisition')]
    public function render()
    {
        return view('livewire.material-requisitions.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
