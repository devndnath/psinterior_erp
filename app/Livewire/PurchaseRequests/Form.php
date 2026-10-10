<?php

namespace App\Livewire\PurchaseRequests;

use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?PurchaseRequest $purchaseRequest = null;

    public string $project_id = '';

    /** @var array<int, array{product_id:string,quantity:string}> */
    public array $items = [];

    public function mount(?PurchaseRequest $purchaseRequest = null): void
    {
        $this->purchaseRequest = $purchaseRequest?->exists ? $purchaseRequest : null;

        Gate::authorize($this->purchaseRequest ? 'update' : 'create', $this->purchaseRequest ?? PurchaseRequest::class);

        if ($this->purchaseRequest) {
            $this->fill([
                'project_id' => (string) $this->purchaseRequest->project_id,
            ]);

            $this->items = $this->purchaseRequest->items->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'quantity' => (string) $item->quantity,
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
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function save(PurchaseRequestService $service): void
    {
        $data = $this->validate();

        if ($this->purchaseRequest) {
            $pr = $service->update($this->purchaseRequest, [], $data['items']);
            $message = "Purchase request {$pr->pr_no} updated.";
        } else {
            $project = Project::findOrFail($data['project_id']);
            $pr = $service->create($project, ['requested_by' => Auth::id()], $data['items']);
            $message = "Purchase request {$pr->pr_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('purchase-requests.show', ['purchaseRequest' => $pr->id], navigate: true);
    }

    #[Title('Purchase Request')]
    public function render()
    {
        return view('livewire.purchase-requests.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
