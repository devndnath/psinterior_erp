<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public PurchaseOrder $purchaseOrder;

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('view', $purchaseOrder);

        $this->purchaseOrder = $purchaseOrder;
    }

    public function send(PurchaseOrderService $service): void
    {
        Gate::authorize('send', $this->purchaseOrder);

        try {
            $this->purchaseOrder = $service->send($this->purchaseOrder);
            $this->dispatch('toast', message: "Purchase order {$this->purchaseOrder->po_no} sent.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancel(PurchaseOrderService $service): void
    {
        Gate::authorize('cancel', $this->purchaseOrder);

        try {
            $this->purchaseOrder = $service->cancel($this->purchaseOrder);
            $this->dispatch('toast', message: "Purchase order {$this->purchaseOrder->po_no} cancelled.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * See App\Http\Controllers\Pdf\InvoicePdfController's docblock — same
     * fix, same reason: a binary PDF can't safely travel through
     * Livewire's own JSON response, so this redirects (full browser
     * navigation, not `navigate: true`) to a plain GET route instead.
     */
    public function downloadPdf()
    {
        Gate::authorize('view', $this->purchaseOrder);

        return $this->redirect(route('purchase-orders.pdf', $this->purchaseOrder));
    }

    public function deletePurchaseOrder(): void
    {
        Gate::authorize('delete', $this->purchaseOrder);

        $no = $this->purchaseOrder->po_no;
        $this->purchaseOrder->delete();

        $this->dispatch('toast', message: "Purchase order {$no} deleted.", type: 'success');
        $this->redirectRoute('purchase-orders.index', navigate: true);
    }

    #[Title('Purchase Order')]
    public function render()
    {
        $this->purchaseOrder->load(['vendor', 'project', 'warehouse', 'items.product', 'goodsReceives.receivedBy']);

        return view('livewire.purchase-orders.show');
    }
}
