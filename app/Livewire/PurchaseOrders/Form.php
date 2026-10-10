<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Handles both an ad-hoc PO (no `?vendor_quotation=` query param — plain
 * vendor/project/warehouse selects plus hand-entered items) and a PO
 * raised from a selected Vendor Quotation (`?vendor_quotation={id}`,
 * vendor/items prefilled from the VQ; project_id/warehouse_id still need
 * picking since the VQ doesn't carry those).
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?PurchaseOrder $purchaseOrder = null;
    public ?VendorQuotation $vendorQuotation = null;

    public string $vendor_id = '';
    public string $project_id = '';
    public string $warehouse_id = '';
    public string $date = '';
    public string $expected_delivery = '';
    public string $discount = '0';
    public string $tax = '0';
    public string $terms = '';

    /** @var array<int, array{product_id:string,qty:string,rate:string}> */
    public array $items = [];

    public function mount(?PurchaseOrder $purchaseOrder = null): void
    {
        $this->purchaseOrder = $purchaseOrder?->exists ? $purchaseOrder : null;

        Gate::authorize($this->purchaseOrder ? 'update' : 'create', $this->purchaseOrder ?? PurchaseOrder::class);

        if ($this->purchaseOrder) {
            $this->fill([
                'vendor_id' => (string) $this->purchaseOrder->vendor_id,
                'project_id' => (string) $this->purchaseOrder->project_id,
                'warehouse_id' => (string) $this->purchaseOrder->warehouse_id,
                'date' => $this->purchaseOrder->date?->format('Y-m-d') ?? '',
                'expected_delivery' => $this->purchaseOrder->expected_delivery?->format('Y-m-d') ?? '',
                'discount' => (string) $this->purchaseOrder->discount,
                'tax' => (string) $this->purchaseOrder->tax,
                'terms' => (string) $this->purchaseOrder->terms,
            ]);

            $this->items = $this->purchaseOrder->items->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'qty' => (string) $item->qty,
                'rate' => (string) $item->rate,
            ])->all();

            return;
        }

        $this->date = now()->toDateString();

        $vendorQuotationId = request()->query('vendor_quotation');

        if ($vendorQuotationId) {
            $this->vendorQuotation = VendorQuotation::with(['items', 'purchaseRequest'])->findOrFail($vendorQuotationId);

            $this->vendor_id = (string) $this->vendorQuotation->vendor_id;
            $this->project_id = (string) $this->vendorQuotation->purchaseRequest->project_id;

            $this->items = $this->vendorQuotation->items->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'qty' => (string) $item->qty,
                'rate' => (string) $item->rate,
            ])->all();

            return;
        }

        $projectId = request()->query('project');

        if ($projectId && Project::whereKey($projectId)->exists()) {
            $this->project_id = (string) $projectId;
        }

        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => '', 'qty' => '1', 'rate' => '0'];
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
            'vendor_id' => ['required', 'exists:vendors,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'date' => ['required', 'date'],
            'expected_delivery' => ['nullable', 'date'],
            'discount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(PurchaseOrderService $service): void
    {
        $data = $this->validate();
        $data['expected_delivery'] = $data['expected_delivery'] ?: null;
        $data['terms'] = $data['terms'] ?: null;

        if ($this->purchaseOrder) {
            $po = $service->update($this->purchaseOrder, $data, $data['items']);
            $message = "Purchase order {$po->po_no} updated.";
        } elseif ($this->vendorQuotation) {
            $po = $service->createFromVendorQuotation($this->vendorQuotation, $data);
            $message = "Purchase order {$po->po_no} created from vendor quotation.";
        } else {
            $po = $service->create($data, $data['items']);
            $message = "Purchase order {$po->po_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('purchase-orders.show', ['purchaseOrder' => $po->id], navigate: true);
    }

    #[Title('Purchase Order')]
    public function render()
    {
        return view('livewire.purchase-orders.form', [
            'vendors' => Vendor::orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
