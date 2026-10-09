<?php

namespace App\Livewire\ChangeOrders;

use App\Models\ChangeOrder;
use App\Models\Project;
use App\Services\ChangeOrderService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?ChangeOrder $changeOrder = null;

    public string $project_id = '';
    public string $description = '';

    /** @var array<int, array{description:string,qty:string,rate:string}> */
    public array $items = [];

    public function mount(?ChangeOrder $changeOrder = null): void
    {
        $this->changeOrder = $changeOrder?->exists ? $changeOrder : null;

        Gate::authorize($this->changeOrder ? 'update' : 'create', $this->changeOrder ?? ChangeOrder::class);

        if ($this->changeOrder) {
            $this->fill([
                'project_id' => (string) $this->changeOrder->project_id,
                'description' => (string) $this->changeOrder->description,
            ]);

            $this->items = $this->changeOrder->items->map(fn ($item) => [
                'description' => $item->description,
                'qty' => (string) $item->qty,
                'rate' => (string) $item->rate,
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
            'description' => ['nullable', 'string', 'max:2000'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric'],
        ];
    }

    public function save(ChangeOrderService $service): void
    {
        $data = $this->validate();

        $changeOrderData = ['description' => $data['description'] ?: null];

        if ($this->changeOrder) {
            $changeOrder = $service->update($this->changeOrder, $changeOrderData, $data['items']);
            $message = "Change order {$changeOrder->co_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $changeOrder = $service->create($project, $changeOrderData, $data['items']);
            $message = "Change order {$changeOrder->co_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('change-orders.show', ['changeOrder' => $changeOrder->id], navigate: true);
    }

    #[Title('Change Order')]
    public function render()
    {
        return view('livewire.change-orders.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
