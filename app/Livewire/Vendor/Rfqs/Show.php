<?php

namespace App\Livewire\Vendor\Rfqs;

use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorQuotationStatus;
use App\Models\PurchaseRequest;
use App\Models\VendorQuotation;
use App\Services\VendorQuotationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Calls VendorQuotationService::create()/
 * update() directly once ownership/open-status is confirmed — the same
 * methods staff use on the vendor's behalf today from
 * App\Livewire\VendorQuotations\Form, never through VendorQuotationPolicy
 * (that Policy is staff-only, same split as every other portal screen).
 */
#[Layout('layouts.vendor-portal')]
class Show extends Component
{
    public PurchaseRequest $purchaseRequest;

    public ?VendorQuotation $myQuotation = null;

    /** @var array<int, array{product_id:string,product_name:string,qty:string,rate:string}> */
    public array $items = [];

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        abort_unless(Auth::guard('vendor')->user()->vendor, 403, 'No vendor record linked to this account.');
        abort_if($purchaseRequest->status === PurchaseRequestStatus::Draft, 404);

        $this->purchaseRequest = $purchaseRequest;
        $this->loadMyQuotation();
    }

    protected function loadMyQuotation(): void
    {
        $vendor = Auth::guard('vendor')->user()->vendor;

        $this->myQuotation = $this->purchaseRequest->vendorQuotations()
            ->where('vendor_id', $vendor->id)
            ->with('items')
            ->first();

        if ($this->myQuotation) {
            $this->items = $this->myQuotation->items->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'product_name' => $item->product?->name ?? '',
                'qty' => (string) $item->qty,
                'rate' => (string) $item->rate,
            ])->all();
        } else {
            $this->items = $this->purchaseRequest->items->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'product_name' => $item->product?->name ?? '',
                'qty' => (string) $item->quantity,
                'rate' => '0',
            ])->all();
        }
    }

    public function canRespond(): bool
    {
        return $this->purchaseRequest->status === PurchaseRequestStatus::SentForQuotation
            && (! $this->myQuotation || $this->myQuotation->status === VendorQuotationStatus::Received);
    }

    protected function rules(): array
    {
        return [
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function submitQuote(VendorQuotationService $service): void
    {
        $vendor = Auth::guard('vendor')->user()->vendor;
        abort_unless($vendor, 403, 'No vendor record linked to this account.');
        abort_unless($this->canRespond(), 403, 'This request is no longer open for quoting.');

        $data = $this->validate();

        $items = array_map(fn ($item) => [
            'product_id' => $item['product_id'],
            'qty' => $item['qty'],
            'rate' => $item['rate'],
        ], $data['items']);

        try {
            if ($this->myQuotation) {
                $service->update($this->myQuotation, $items);
            } else {
                $service->create($this->purchaseRequest, ['vendor_id' => $vendor->id], $items);
            }
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->purchaseRequest->refresh();
        $this->loadMyQuotation();

        $this->dispatch('toast', message: 'Your quotation has been submitted.', type: 'success');
    }

    #[Title('Respond to RFQ')]
    public function render()
    {
        $this->purchaseRequest->load(['project', 'items.product']);

        return view('livewire.vendor.rfqs.show', [
            'canRespond' => $this->canRespond(),
        ]);
    }
}
