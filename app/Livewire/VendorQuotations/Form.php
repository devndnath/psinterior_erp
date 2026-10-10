<?php

namespace App\Livewire\VendorQuotations;

use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Services\VendorQuotationService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Always reached in the context of a Purchase Request
 * (`?purchase_request={id}`, the same query-param pattern
 * ChangeOrders/Form uses for `?project=`). There is no separate Index
 * or Show — a vendor quotation is always viewed from its parent
 * request's Show page.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public PurchaseRequest $purchaseRequest;

    public string $vendor_id = '';

    /** @var array<int, array{product_id:string,qty:string,rate:string}> */
    public array $items = [];

    public function mount(): void
    {
        Gate::authorize('create', VendorQuotation::class);

        $this->purchaseRequest = PurchaseRequest::findOrFail(request()->query('purchase_request'));

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
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(VendorQuotationService $service): void
    {
        $data = $this->validate();

        try {
            $vq = $service->create($this->purchaseRequest, ['vendor_id' => $data['vendor_id']], $data['items']);
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: 'Vendor quotation recorded.', type: 'success');
        $this->redirectRoute('purchase-requests.show', ['purchaseRequest' => $this->purchaseRequest->id], navigate: true);
    }

    #[Title('Vendor Quotation')]
    public function render()
    {
        return view('livewire.vendor-quotations.form', [
            'vendors' => Vendor::orderBy('company_name')->get(['id', 'company_name']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
