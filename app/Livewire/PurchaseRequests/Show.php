<?php

namespace App\Livewire\PurchaseRequests;

use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use App\Services\VendorQuotationService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public PurchaseRequest $purchaseRequest;

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        Gate::authorize('view', $purchaseRequest);

        $this->purchaseRequest = $purchaseRequest;
    }

    public function sendForQuotation(PurchaseRequestService $service): void
    {
        Gate::authorize('sendForQuotation', $this->purchaseRequest);

        try {
            $this->purchaseRequest = $service->sendForQuotation($this->purchaseRequest);
            $this->dispatch('toast', message: "Purchase request {$this->purchaseRequest->pr_no} sent for quotation.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancel(PurchaseRequestService $service): void
    {
        Gate::authorize('cancel', $this->purchaseRequest);

        try {
            $this->purchaseRequest = $service->cancel($this->purchaseRequest);
            $this->dispatch('toast', message: "Purchase request {$this->purchaseRequest->pr_no} cancelled.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function selectWinner(VendorQuotationService $service, int $vendorQuotationId): void
    {
        $vendorQuotation = $this->purchaseRequest->vendorQuotations()->findOrFail($vendorQuotationId);
        Gate::authorize('selectWinner', $vendorQuotation);

        $service->selectWinner($vendorQuotation);
        $this->purchaseRequest->refresh();

        $this->dispatch('toast', message: 'Vendor quotation selected as winner.', type: 'success');
    }

    #[Title('Purchase Request')]
    public function render()
    {
        $this->purchaseRequest->load(['project', 'items.product', 'vendorQuotations.vendor']);

        return view('livewire.purchase-requests.show');
    }
}
